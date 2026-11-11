export function registerMergeFormats(Quill) {
    const { Attributor, Scope } = Quill.import('parchment');
    for (const [name, attribute, valid] of [
        ['cell-colspan', 'colspan', value => /^\d+$/.test(value) && Number(value) >= 1 && Number(value) <= 200],
        ['cell-rowspan', 'rowspan', value => /^\d+$/.test(value) && Number(value) >= 1 && Number(value) <= 200],
        ['cell-merge-widths', 'data-merge-widths', value => value.split(',').length <= 200 && value.split(',').every(part => /^\d+(\.\d+)?%$/.test(part) && parseFloat(part) > 0 && parseFloat(part) <= 100)],
        ['cell-height', 'height', value => /^\d+$/.test(value) && Number(value) >= 1 && Number(value) <= 400000],
        ['table-row-heights', 'data-table-row-heights', value => value.split(',').length <= 200 && value.split(',').every(height => /^\d+$/.test(height) && Number(height) >= 24 && Number(height) <= 2000)],
        ['cell-covered', 'data-cell-covered', value => value === 'true'],
        ['cell-original-width', 'data-original-width', value => /^\d+(\.\d+)?%$/.test(value) && parseFloat(value) > 0 && parseFloat(value) <= 100],
        ['table-width', 'data-table-width', value => /^\d+(\.\d+)?%$/.test(value) && parseFloat(value) > 0 && parseFloat(value) <= 100],
        ['table-offset', 'data-table-offset', value => /^\d+(\.\d+)?%$/.test(value) && parseFloat(value) >= 0 && parseFloat(value) <= 100],
    ]) {
        class CellAttribute extends Attributor {
            canAdd(node, value) { return node.tagName === 'TD' && valid(String(value)) && super.canAdd(node, value); }
        }
        Quill.register(new CellAttribute(name, attribute, { scope: Scope.BLOCK }));
    }
    const Table = Quill.import('formats/table-container');
    class SizedTable extends Table {
        optimize(context) {
            super.optimize(context);
            const rows = Array.from(this.domNode.rows);
            const covered = new Map();
            rows.forEach((row, r) => Array.from(row.cells).forEach((cell, c) => {
                if (cell.colSpan <= 1 && cell.rowSpan <= 1) return;
                const widths = cell.getAttribute('data-merge-widths')?.split(',');
                for (let rr = r; rr < r + cell.rowSpan; rr++) {
                    for (let cc = c; cc < c + cell.colSpan; cc++) {
                        if (rr !== r || cc !== c) covered.set(`${rr}:${cc}`, widths?.[cc - c]);
                    }
                }
            }));
            rows.forEach((row, r) => Array.from(row.cells).forEach((cell, c) => {
                const key = `${r}:${c}`, hidden = covered.has(key), blot = Quill.find(cell);
                if (hidden !== cell.hasAttribute('data-cell-covered')) blot.format('cell-covered', hidden ? 'true' : false);
                if (hidden && covered.get(key) && cell.getAttribute('width') !== covered.get(key)) blot.format('column-width', covered.get(key));
            }));
            const first = this.domNode.querySelector('td');
            const heights = first?.getAttribute('data-table-row-heights')?.split(',').map(Number);
            rows.forEach((row, r) => {
                const height = heights?.[r];
                const style = height ? `${height}px` : '';
                if (row.style.height !== style) row.style.height = style;
                Array.from(row.cells).forEach(cell => {
                    const size = height ? heights.slice(r, r + cell.rowSpan).reduce((a, b) => a + b, 0) : null;
                    const blot = Quill.find(cell);
                    if (size && cell.getAttribute('height') !== String(size)) blot.format('cell-height', String(size));
                    if (!size && cell.hasAttribute('height')) blot.format('cell-height', false);
                    const cellStyle = size ? `${size}px` : '';
                    if (cell.style.height !== cellStyle) cell.style.height = cellStyle;
                });
            });
            const width = first?.getAttribute('data-table-width') || '';
            const offset = first?.getAttribute('data-table-offset') || '';
            if (this.domNode.style.width !== width) this.domNode.style.width = width;
            if (this.domNode.style.marginLeft !== offset) this.domNode.style.marginLeft = offset;
        }
    }
    Quill.register(SizedTable, true);
}

export function columnWidths(table) {
    const first = Array.from(table.rows[0].cells);
    const total = table.getBoundingClientRect().width;
    const widths = first.map(cell => parseFloat(cell.getAttribute('data-original-width') || cell.getAttribute('width')) || cell.getBoundingClientRect().width / total * 100 / cell.colSpan);
    first.forEach((cell, c) => {
        const merged = cell.getAttribute('data-merge-widths')?.split(',').map(parseFloat);
        if (merged) merged.forEach((width, offset) => { widths[c + offset] = width; });
    });
    const sum = widths.reduce((a, b) => a + b, 0);
    return widths.map(width => width / sum * 100);
}

export function applyCellChanges(editor, table, attributes) {
    const Delta = editor.constructor.import('delta');
    const change = new Delta();
    let position = 0;
    Array.from(table.rows).forEach((row, r) => Array.from(row.cells).forEach((cell, c) => {
        const formats = attributes(cell, r, c);
        if (!formats) return;
        const blot = editor.constructor.find(cell);
        const index = blot.offset(editor.scroll) + blot.length() - 1;
        change.retain(index - position).retain(1, formats);
        position = index + 1;
    }));
    editor.history.cutoff();
    editor.updateContents(change, 'user');
    editor.history.cutoff();
}

const selections = new WeakMap();
const selectors = new WeakMap();
export function selectedCells(editor) {
    const selection = selections.get(editor);
    if (selection && (!selection.table.isConnected || selection.cells.some(cell => !selection.table.contains(cell)))) {
        clearCellSelection(editor);
        return undefined;
    }
    return selection;
}
export function clearCellSelection(editor) {
    editor.root.querySelectorAll('[data-cell-selected]').forEach(cell => cell.removeAttribute('data-cell-selected'));
    selections.delete(editor);
}

export function addCellSelection(editor) {
    const root = editor.root;
    let start = null;
    let pointer = null;
    function select(first, last, explicit = false) {
        const table = first.closest('table');
        if (last.closest('table') !== table) return;
        const bounds = { top: Math.min(first.parentElement.rowIndex, last.parentElement.rowIndex), bottom: Math.max(first.parentElement.rowIndex + first.rowSpan - 1, last.parentElement.rowIndex + last.rowSpan - 1), left: Math.min(first.cellIndex, last.cellIndex), right: Math.max(first.cellIndex + first.colSpan - 1, last.cellIndex + last.colSpan - 1) };
        let expanded;
        do {
            expanded = false;
            Array.from(table.rows).forEach((row, r) => Array.from(row.cells).forEach((cell, c) => {
                if (cell.hasAttribute('data-cell-covered')) return;
                const bottom = r + cell.rowSpan - 1, right = c + cell.colSpan - 1;
                if (r > bounds.bottom || bottom < bounds.top || c > bounds.right || right < bounds.left) return;
                const next = { top: Math.min(bounds.top, r), bottom: Math.max(bounds.bottom, bottom), left: Math.min(bounds.left, c), right: Math.max(bounds.right, right) };
                if (Object.keys(next).some(key => next[key] !== bounds[key])) { Object.assign(bounds, next); expanded = true; }
            }));
        } while (expanded);
        clearCellSelection(editor);
        const cells = [];
        Array.from(table.rows).forEach((row, r) => Array.from(row.cells).forEach((cell, c) => {
            if (r >= bounds.top && r <= bounds.bottom && c >= bounds.left && c <= bounds.right && !cell.hasAttribute('data-cell-covered')) {
                cells.push(cell);
                if (first !== last || explicit) cell.setAttribute('data-cell-selected', 'true');
            }
        }));
        selections.set(editor, { table, cells, bounds, anchor: first, explicit: explicit || first !== last, rectangular: true });
    }
    selectors.set(editor, select);
    root.addEventListener('pointerdown', event => {
        const cell = event.target.closest('td');
        if (!cell || event.button !== 0) { clearCellSelection(editor); return; }
        const previous = selectedCells(editor);
        if (event.ctrlKey || event.metaKey) {
            event.preventDefault();
            const table = cell.closest('table');
            const cells = previous?.table === table ? [...previous.cells] : [];
            const index = cells.indexOf(cell);
            if (index >= 0) cells.splice(index, 1); else cells.push(cell);
            clearCellSelection(editor);
            if (cells.length) {
                cells.sort((a, b) => a.parentElement.rowIndex - b.parentElement.rowIndex || a.cellIndex - b.cellIndex);
                const bounds = { top: Math.min(...cells.map(c => c.parentElement.rowIndex)), bottom: Math.max(...cells.map(c => c.parentElement.rowIndex + c.rowSpan - 1)), left: Math.min(...cells.map(c => c.cellIndex)), right: Math.max(...cells.map(c => c.cellIndex + c.colSpan - 1)) };
                cells.forEach(c => c.setAttribute('data-cell-selected', 'true'));
                const area = cells.reduce((sum, c) => sum + c.rowSpan * c.colSpan, 0);
                selections.set(editor, { table, cells, bounds, anchor: cells[0], explicit: true, rectangular: area === (bounds.bottom - bounds.top + 1) * (bounds.right - bounds.left + 1) });
            }
            start = null;
            const blot = editor.constructor.find(cell);
            editor.setSelection(blot.offset(editor.scroll), 0, 'silent');
            return;
        }
        start = event.shiftKey && previous?.table === cell.closest('table') ? previous.anchor : cell;
        pointer = event.pointerId;
        select(start, cell);
        if (event.shiftKey) event.preventDefault();
    });
    root.addEventListener('pointermove', event => {
        if (!start || event.pointerId !== pointer || !(event.buttons & 1)) return;
        const cell = document.elementFromPoint(event.clientX, event.clientY)?.closest('td');
        if (cell && cell !== start && root.contains(cell) && cell.closest('table') === start.closest('table')) {
            event.preventDefault();
            select(start, cell);
        }
    });
    const finish = () => {
        if (start && selectedCells(editor)?.cells.length > 1) {
            const blot = editor.constructor.find(start);
            editor.setSelection(blot.offset(editor.scroll), 0, 'silent');
        }
        start = null; pointer = null;
    };
    root.addEventListener('pointerup', finish);
    root.addEventListener('pointercancel', finish);
    root.addEventListener('copy', event => handleClipboardEvent(editor, event), true);
    root.addEventListener('cut', event => handleClipboardEvent(editor, event), true);
    root.addEventListener('keydown', event => {
        const selection = selectedCells(editor);
        if (event.key === ' ' && selection && (event.ctrlKey || event.metaKey || event.shiftKey)) {
            event.preventDefault(); event.stopImmediatePropagation();
            selectTablePart(editor, event.ctrlKey || event.metaKey ? 'column' : 'row');
            return;
        }
        if (event.ctrlKey || event.metaKey) {
            if (event.key.toLowerCase() === 'a' && selection) {
                event.preventDefault(); event.stopImmediatePropagation();
                selectTablePart(editor, 'table');
            }
            return;
        }
        if (selection?.explicit && ['Delete', 'Backspace'].includes(event.key)) {
            event.preventDefault(); event.stopImmediatePropagation();
            clearSelectedContents(editor);
            return;
        }
        if (event.key.length === 1 || ['Backspace', 'Delete', 'Enter', 'Escape'].includes(event.key)) clearCellSelection(editor);
    }, true);
}

export function mergeCells(editor) {
    const selection = selectedCells(editor);
    if (!selection || !selection.rectangular || selection.cells.length < 2) return;
    const { table, bounds, cells } = selection;
    const widths = columnWidths(table);
    const Delta = editor.constructor.import('delta');
    let contents = new Delta();
    for (const cell of cells) {
        const blot = editor.constructor.find(cell);
        const content = editor.getContents(blot.offset(editor.scroll), blot.length() - 1);
        if (content.length()) {
            if (contents.length()) contents.insert(' ');
            contents = contents.concat(content);
        }
    }
    let change = new Delta(), position = 0;
    Array.from(table.rows).forEach((row, r) => Array.from(row.cells).forEach((cell, c) => {
        if (r < bounds.top || r > bounds.bottom || c < bounds.left || c > bounds.right) return;
        const blot = editor.constructor.find(cell), index = blot.offset(editor.scroll);
        const anchor = r === bounds.top && c === bounds.left;
        const formats = { ...blot.formats(), 'cell-colspan': anchor ? bounds.right - bounds.left + 1 : null, 'cell-rowspan': anchor ? bounds.bottom - bounds.top + 1 : null, 'cell-covered': anchor ? null : 'true', 'column-width': `${(anchor ? widths.slice(bounds.left, bounds.right + 1).reduce((a, b) => a + b, 0) : widths[c]).toFixed(4)}%`, 'cell-merge-widths': anchor ? widths.slice(bounds.left, bounds.right + 1).map(width => `${width.toFixed(4)}%`).join(',') : null, 'cell-original-width': anchor ? `${widths[c].toFixed(4)}%` : null };
        change = change.retain(index - position).delete(blot.length()).concat(anchor ? contents : new Delta()).insert('\n', formats);
        position = index + blot.length();
    }));
    clearCellSelection(editor);
    editor.history.cutoff(); editor.updateContents(change, 'user'); editor.history.cutoff();
}

export function unmergeCells(editor) {
    const selection = selectedCells(editor);
    const merged = selection?.cells.filter(cell => cell.colSpan > 1 || cell.rowSpan > 1);
    if (!merged?.length) return;
    const table = selection.table;
    const rectangles = merged.map(cell => ({ top: cell.parentElement.rowIndex, left: cell.cellIndex,
        bottom: cell.parentElement.rowIndex + cell.rowSpan - 1, right: cell.cellIndex + cell.colSpan - 1 }));
    const widths = columnWidths(table);
    clearCellSelection(editor);
    applyCellChanges(editor, table, (current, r, c) => rectangles.some(({ top, bottom, left, right }) => r >= top && r <= bottom && c >= left && c <= right) ? {
        'cell-colspan': null, 'cell-rowspan': null, 'cell-covered': null, 'cell-merge-widths': null, 'cell-original-width': null, 'column-width': `${widths[c].toFixed(4)}%`,
    } : null);
}

export function editTableStructure(editor, action, range) {
    const [blot, rowBlot, cellBlot] = editor.getModule('table').getTable(range);
    if (!blot) return;
    const table = blot.domNode, active = cellBlot.domNode;
    const rowIndex = active.parentElement.rowIndex, columnIndex = active.cellIndex;
    const widths = columnWidths(table);
    const Delta = editor.constructor.import('delta');
    const rowHeights = table.querySelector('td').getAttribute('data-table-row-heights')?.split(',').map(Number);
    const groups = [];
    const matrix = Array.from(table.rows, (row, r) => Array.from(row.cells, (cell, c) => {
        const cellBlot = editor.constructor.find(cell);
        const data = { contents: editor.getContents(cellBlot.offset(editor.scroll), cellBlot.length() - 1), formats: { ...cellBlot.formats() } };
        if (cell.colSpan > 1 || cell.rowSpan > 1) groups.push({ top: r, left: c, rows: cell.rowSpan, columns: cell.colSpan, contents: data.contents });
        return data;
    }));
    const isRow = action.includes('Row'), insert = action.startsWith('insert');
    const selection = selectedCells(editor);
    const selected = selection?.table === table && selection.explicit ? selection.cells : [active];
    const removed = new Set(selected.flatMap(cell => Array.from({ length: isRow ? cell.rowSpan : cell.colSpan }, (_, i) => (isRow ? cell.parentElement.rowIndex : cell.cellIndex) + i)));
    const index = isRow ? rowIndex + (action === 'insertRowBelow' ? active.rowSpan : 0)
        : columnIndex + (action === 'insertColumnRight' ? active.colSpan : 0);
    const empty = rowId => ({ contents: new Delta(), formats: { table: rowId } });
    if (isRow) {
        if (insert) {
            const id = `row-${Math.random().toString(36).slice(2, 10)}`;
            matrix.splice(index, 0, widths.map(() => empty(id)));
            rowHeights?.splice(index, 0, 24);
        } else {
            const keep = matrix.filter((row, r) => !removed.has(r));
            matrix.splice(0, matrix.length, ...keep);
            if (rowHeights) rowHeights.splice(0, rowHeights.length, ...rowHeights.filter((height, r) => !removed.has(r)));
        }
    } else {
        matrix.forEach(row => {
            if (insert) row.splice(index, 0, empty(row[0].formats.table));
            else row.splice(0, row.length, ...row.filter((cell, c) => !removed.has(c)));
        });
        if (insert) widths.splice(index, 0, 100 / widths.length);
        else widths.splice(0, widths.length, ...widths.filter((width, c) => !removed.has(c)));
    }
    if (!matrix.length || !widths.length) {
        clearCellSelection(editor);
        editor.getModule('table').deleteTable();
        return;
    }
    const sum = widths.reduce((a, b) => a + b, 0);
    const normalized = widths.map(value => value / sum * 100);
    const width = table.querySelector('td').getAttribute('data-table-width');
    const offset = table.querySelector('td').getAttribute('data-table-offset');
    matrix.forEach(row => row.forEach((cell, c) => {
        Object.assign(cell.formats, { 'cell-covered': null, 'cell-colspan': null, 'cell-rowspan': null, 'cell-merge-widths': null, 'cell-original-width': null,
            'column-width': `${normalized[c].toFixed(4)}%`, 'table-width': width, 'table-offset': offset, 'table-row-heights': rowHeights?.join(',') || null });
    }));
    for (const group of groups) {
        const start = isRow ? 'top' : 'left', span = isRow ? 'rows' : 'columns';
        if (insert) {
            if (index <= group[start]) group[start]++;
            else if (index < group[start] + group[span]) group[span]++;
        } else {
            const deleted = [...removed];
            const overlap = deleted.filter(i => i >= group[start] && i < group[start] + group[span]).length;
            group[start] -= deleted.filter(i => i < group[start]).length;
            group[span] -= overlap;
        }
        if (!group.rows || !group.columns) continue;
        for (let r = group.top; r < group.top + group.rows; r++) {
            for (let c = group.left; c < group.left + group.columns; c++) {
                const cell = matrix[r][c], anchor = r === group.top && c === group.left;
                cell.contents = anchor ? group.contents : new Delta();
                Object.assign(cell.formats, { 'cell-covered': anchor ? null : 'true',
                    'cell-colspan': anchor ? group.columns : null, 'cell-rowspan': anchor ? group.rows : null,
                    'column-width': `${(anchor ? normalized.slice(c, c + group.columns).reduce((a, b) => a + b, 0) : normalized[c]).toFixed(4)}%`,
                    'cell-merge-widths': anchor ? normalized.slice(c, c + group.columns).map(width => `${width.toFixed(4)}%`).join(',') : null,
                    'cell-original-width': anchor && group.columns > 1 ? `${normalized[c].toFixed(4)}%` : null });
            }
        }
    }
    let replacement = new Delta();
    matrix.forEach(row => row.forEach(cell => {
        const formats = Object.fromEntries(Object.entries(cell.formats).filter(([, value]) => value != null));
        replacement = replacement.concat(cell.contents).insert('\n', formats);
    }));
    const start = blot.offset(editor.scroll), length = blot.length();
    clearCellSelection(editor);
    editor.history.cutoff();
    editor.updateContents(new Delta().retain(start).delete(length).concat(replacement), 'user');
    editor.history.cutoff();
    editor.setSelection(Math.min(start, editor.getLength() - 1), 0, 'silent');
}

export function selectTablePart(editor, part) {
    const current = selectedCells(editor);
    const active = current?.anchor || editor.getModule('table').getTable(editor.getSelection(true))[2]?.domNode;
    if (!active) return;
    const table = active.closest('table');
    const top = current?.bounds.top ?? active.parentElement.rowIndex;
    const bottom = current?.bounds.bottom ?? top + active.rowSpan - 1;
    const left = current?.bounds.left ?? active.cellIndex;
    const right = current?.bounds.right ?? left + active.colSpan - 1;
    const first = table.rows[part === 'column' || part === 'table' ? 0 : top].cells[part === 'row' || part === 'table' ? 0 : left];
    const lastRow = table.rows[part === 'column' || part === 'table' ? table.rows.length - 1 : bottom];
    const last = lastRow.cells[part === 'row' || part === 'table' ? lastRow.cells.length - 1 : right];
    selectors.get(editor)(first, last, true);
    selectedCells(editor).kind = part;
    const blot = editor.constructor.find(selectedCells(editor).cells[0]);
    editor.setSelection(blot.offset(editor.scroll), 0, 'silent');
}

export function clearSelectedContents(editor) {
    const selection = selectedCells(editor);
    if (!selection) return;
    const Delta = editor.constructor.import('delta');
    const change = new Delta();
    let position = 0;
    for (const cell of selection.cells) {
        const blot = editor.constructor.find(cell), index = blot.offset(editor.scroll), length = blot.length() - 1;
        change.retain(index - position).delete(length);
        position = index + length;
    }
    editor.history.cutoff(); editor.updateContents(change, 'user'); editor.history.cutoff();
    editor.setSelection(Math.min(editor.constructor.find(selection.cells[0]).offset(editor.scroll), editor.getLength() - 1), 0, 'silent');
}

export function selectedClipboardData(editor) {
    const selection = selectedCells(editor);
    if (!selection) return null;
    const { table, bounds, cells } = selection;
    const selected = new Set(cells), widths = columnWidths(table);
    const totalWidth = widths.slice(bounds.left, bounds.right + 1).reduce((a, b) => a + b, 0);
    const copy = document.createElement('table');
    copy.style.borderCollapse = 'collapse';
    const body = document.createElement('tbody');
    const text = [];
    for (let r = bounds.top; r <= bounds.bottom; r++) {
        const row = document.createElement('tr'), values = [];
        for (let c = bounds.left; c <= bounds.right; c++) {
            const original = table.rows[r].cells[c];
            const covered = original.hasAttribute('data-cell-covered') && cells.some(cell =>
                r >= cell.parentElement.rowIndex && r < cell.parentElement.rowIndex + cell.rowSpan &&
                c >= cell.cellIndex && c < cell.cellIndex + cell.colSpan);
            const cell = selected.has(original) || covered ? original.cloneNode(true) : document.createElement('td');
            cell.removeAttribute('data-cell-selected');
            cell.removeAttribute('data-table-width'); cell.removeAttribute('data-table-offset');
            const heights = cell.getAttribute('data-table-row-heights');
            if (heights) cell.setAttribute('data-table-row-heights', heights.split(',').slice(bounds.top, bounds.bottom + 1).join(','));
            cell.querySelectorAll('[contenteditable]').forEach(node => node.removeAttribute('contenteditable'));
            cell.style.border = '1px solid #000';
            if (covered) cell.style.display = 'none';
            else cell.setAttribute('width', `${(widths.slice(c, c + cell.colSpan).reduce((a, b) => a + b, 0) / totalWidth * 100).toFixed(4)}%`);
            const plain = cell.cloneNode(true);
            plain.querySelectorAll('.document-cell-break').forEach(node => node.replaceWith(document.createTextNode('\n')));
            const value = covered ? '' : plain.textContent.replaceAll('\uFEFF', '');
            values.push(/[\t\n"]/.test(value) ? `"${value.replaceAll('"', '""')}"` : value);
            row.append(cell);
        }
        body.append(row); text.push(values.join('\t'));
    }
    copy.append(body);
    return { html: copy.outerHTML, text: text.join('\n') };
}

function handleClipboardEvent(editor, event) {
    if (!selectedCells(editor)?.explicit || !event.clipboardData) return;
    const data = selectedClipboardData(editor);
    event.clipboardData.setData('text/html', data.html);
    event.clipboardData.setData('text/plain', data.text);
    event.preventDefault(); event.stopImmediatePropagation();
    if (event.type === 'cut') clearSelectedContents(editor);
}

export async function copySelectedCells(editor, cut = false) {
    const data = selectedClipboardData(editor);
    if (!data) return false;
    selectedCells(editor).explicit = true;
    selectedCells(editor).cells.forEach(cell => cell.setAttribute('data-cell-selected', 'true'));
    let copied = false;
    if (navigator.clipboard?.write && window.ClipboardItem) {
        try {
            await navigator.clipboard.write([new ClipboardItem({
                'text/html': new Blob([data.html], { type: 'text/html' }),
                'text/plain': new Blob([data.text], { type: 'text/plain' }),
            })]);
            copied = true;
        } catch { /* Fall back to the browser's copy command. */ }
    }
    if (!copied) {
        const listener = event => {
            event.clipboardData.setData('text/html', data.html);
            event.clipboardData.setData('text/plain', data.text);
            event.preventDefault(); event.stopImmediatePropagation();
        };
        editor.focus();
        document.addEventListener('copy', listener, true);
        try { copied = document.execCommand('copy'); }
        finally { document.removeEventListener('copy', listener, true); }
    }
    if (copied && cut) clearSelectedContents(editor);
    return copied;
}

export function selectTableHeaders(editor, table, part, indices = []) {
    const rows = Array.from(table.rows);
    if (!rows.length) return;
    if (part === 'table') {
        selectors.get(editor)(rows[0].cells[0], rows.at(-1).cells[rows.at(-1).cells.length - 1], true);
    } else {
        const union = new Set();
        for (const index of indices) {
            const first = part === 'row' ? rows[index]?.cells[0] : rows[0].cells[index];
            const last = part === 'row' ? rows[index]?.cells[rows[index].cells.length - 1] : rows.at(-1).cells[index];
            if (!first || !last) continue;
            selectors.get(editor)(first, last, true);
            selectedCells(editor).cells.forEach(cell => union.add(cell));
        }
        clearCellSelection(editor);
        if (!union.size) return;
        const cells = [...union].sort((a, b) => a.parentElement.rowIndex - b.parentElement.rowIndex || a.cellIndex - b.cellIndex);
        const bounds = { top: Math.min(...cells.map(c => c.parentElement.rowIndex)), bottom: Math.max(...cells.map(c => c.parentElement.rowIndex + c.rowSpan - 1)), left: Math.min(...cells.map(c => c.cellIndex)), right: Math.max(...cells.map(c => c.cellIndex + c.colSpan - 1)) };
        cells.forEach(cell => cell.setAttribute('data-cell-selected', 'true'));
        const area = cells.reduce((sum, cell) => sum + cell.rowSpan * cell.colSpan, 0);
        selections.set(editor, { table, cells, bounds, anchor: cells[0], explicit: true, rectangular: area === (bounds.bottom - bounds.top + 1) * (bounds.right - bounds.left + 1) });
    }
    const selection = selectedCells(editor);
    selection.kind = part;
    selection.headerKind = part;
    selection.headerIndices = [...indices];
    const blot = editor.constructor.find(selection.cells[0]);
    editor.setSelection(blot.offset(editor.scroll), 0, 'silent');
}
