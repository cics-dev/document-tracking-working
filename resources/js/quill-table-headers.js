import { columnWidths, selectedCells, selectTableHeaders } from './quill-table-cells';

function columnName(index) {
    let name = '';
    for (let n = index + 1; n > 0; n = Math.floor((n - 1) / 26)) name = String.fromCharCode(65 + (n - 1) % 26) + name;
    return name;
}

export function addTableHeaders(editor) {
    const root = editor.root, container = editor.container;
    const layer = document.createElement('div');
    layer.className = 'document-table-headers';
    layer.setAttribute('role', 'group');
    layer.setAttribute('aria-label', 'Table row and column selectors');
    container.append(layer);
    const entries = new Map();
    let frame = null, drag = null;

    function choose(entry, part, index, event) {
        if (part === 'table') { selectTableHeaders(editor, entry.table, part); schedule(); return; }
        const previous = selectedCells(editor);
        const anchor = event.shiftKey && entry.anchor?.part === part ? entry.anchor.index : index;
        let indices = Array.from({ length: Math.abs(index - anchor) + 1 }, (_, i) => Math.min(index, anchor) + i);
        const base = (event.ctrlKey || event.metaKey) && previous?.table === entry.table && previous.headerKind === part ? previous.headerIndices : [];
        if (event.ctrlKey || event.metaKey) indices = [...new Set([...base, ...indices])].filter(i => i !== index || !base.includes(index));
        if (!event.shiftKey) entry.anchor = { part, index };
        selectTableHeaders(editor, entry.table, part, indices);
        drag = { entry, part, anchor, base: event.ctrlKey || event.metaKey ? base : [] };
        schedule();
    }
    function makeButton(entry, part, index, text) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `document-table-header document-table-header-${part}`;
        button.dataset.headerPart = part;
        button.dataset.headerIndex = index;
        button.documentTable = entry.table;
        button.textContent = text;
        button.setAttribute('aria-label', part === 'table' ? 'Select entire table' : `Select ${part} ${part === 'column' ? columnName(index) : index + 1}`);
        button.title = part === 'table' ? 'Select all cells' : `Select ${part} (Shift-click for a range; Ctrl-click to add)`;
        button.addEventListener('pointerdown', event => {
            if (event.button !== 0) return;
            event.preventDefault(); event.stopPropagation();
            choose(entry, part, index, event);
            layer.setPointerCapture(event.pointerId);
        });
        button.addEventListener('click', event => {
            if (event.detail !== 0) return;
            choose(entry, part, index, event);
            drag = null;
        });
        entry.panel.append(button);
        return button;
    }
    layer.addEventListener('pointermove', event => {
        if (!drag || !(event.buttons & 1)) return;
        const button = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-header-part]');
        if (!button || !drag.entry.panel.contains(button) || button.dataset.headerPart !== drag.part) return;
        const index = Number(button.dataset.headerIndex);
        const indices = Array.from({ length: Math.abs(index - drag.anchor) + 1 }, (_, i) => Math.min(index, drag.anchor) + i);
        selectTableHeaders(editor, drag.entry.table, drag.part, [...new Set([...drag.base, ...indices])]);
        schedule();
    });
    const finish = event => {
        drag = null;
        if (layer.hasPointerCapture(event.pointerId)) layer.releasePointerCapture(event.pointerId);
    };
    layer.addEventListener('pointerup', finish);
    layer.addEventListener('pointercancel', finish);

    function position(button, left, top, width, height, selected) {
        button.hidden = width <= 0 || height <= 0;
        Object.assign(button.style, { left: `${left}px`, top: `${top}px`, width: `${width}px`, height: `${height}px` });
        button.setAttribute('aria-pressed', String(selected));
    }
    function refresh() {
        frame = null;
        if (!root.isConnected) {
            mutations.disconnect(); sizes.disconnect(); layer.remove();
            return;
        }
        const tables = [...root.querySelectorAll('table')];
        for (const [table, entry] of entries) {
            if (!tables.includes(table)) { entry.panel.remove(); sizes.unobserve(table); entries.delete(table); }
        }
        const rect = container.getBoundingClientRect(), viewport = root.getBoundingClientRect();
        const parent = { left: rect.left + container.clientLeft, top: rect.top + container.clientTop };
        for (const table of tables) {
            const rows = Array.from(table.rows), count = rows[0]?.cells.length || 0;
            if (!count) continue;
            let entry = entries.get(table);
            if (!entry || entry.rows.length !== rows.length || entry.columns.length !== count) {
                entry?.panel.remove();
                const panel = document.createElement('div');
                panel.className = 'document-table-header-group';
                layer.append(panel);
                entry = { table, panel, rows: [], columns: [] };
                entry.corner = makeButton(entry, 'table', 0, '');
                entry.columns = Array.from({ length: count }, (_, c) => makeButton(entry, 'column', c, columnName(c)));
                entry.rows = rows.map((row, r) => makeButton(entry, 'row', r, String(r + 1)));
                entries.set(table, entry); sizes.observe(table);
            }
            const rect = table.getBoundingClientRect();
            entry.panel.hidden = rect.bottom <= viewport.top || rect.top >= viewport.bottom;
            if (entry.panel.hidden) continue;
            const selection = selectedCells(editor), selected = new Set();
            if (selection?.table === table && selection.explicit) {
                selection.cells.forEach(cell => {
                    for (let r = cell.parentElement.rowIndex; r < cell.parentElement.rowIndex + cell.rowSpan; r++) {
                        for (let c = cell.cellIndex; c < cell.cellIndex + cell.colSpan; c++) selected.add(`${r}:${c}`);
                    }
                });
            }
            const headerTop = Math.max(viewport.top + 1, rect.top - 22) - parent.top;
            const gutter = Math.min(26, Math.max(14, rect.left - viewport.left - 1));
            position(entry.corner, rect.left - parent.left - gutter, headerTop, gutter, 22, selected.size === rows.length * count);
            let x = rect.left;
            const widths = columnWidths(table);
            entry.columns.forEach((button, c) => {
                const width = rect.width * widths[c] / 100;
                const left = Math.max(viewport.left, x), right = Math.min(viewport.right, x + width);
                position(button, left - parent.left, headerTop, right - left, 22, rows.every((row, r) => selected.has(`${r}:${c}`)));
                x += width;
            });
            const weights = rows.map(row => Math.max(24, row.getBoundingClientRect().height));
            const total = weights.reduce((a, b) => a + b, 0);
            let y = rect.top;
            entry.rows.forEach((button, r) => {
                const height = rect.height * weights[r] / total;
                const top = Math.max(parent.top + headerTop + 22, y), bottom = Math.min(viewport.bottom, y + height);
                position(button, rect.left - parent.left - gutter, top - parent.top, gutter, bottom - top, Array.from({ length: count }, (_, c) => selected.has(`${r}:${c}`)).every(Boolean));
                y += height;
            });
        }
    }
    function schedule() { if (frame === null) frame = requestAnimationFrame(refresh); }
    const mutations = new MutationObserver(schedule);
    mutations.observe(root, { subtree: true, childList: true, attributes: true, characterData: true });
    const sizes = new ResizeObserver(schedule);
    sizes.observe(root); sizes.observe(container);
    root.addEventListener('scroll', schedule);
    editor.on('selection-change', schedule);
    schedule();
}
