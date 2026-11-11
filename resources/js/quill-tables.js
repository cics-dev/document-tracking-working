import { selectedCells, mergeCells, unmergeCells, editTableStructure, clearCellSelection, selectTablePart, clearSelectedContents, copySelectedCells } from './quill-table-cells';

export function addTableControls(editor, Swal) {
    const table = editor.getModule('table');
    const controls = document.createElement('span');
    controls.className = 'ql-formats document-table-controls';
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'document-table-toggle';
    button.title = 'Table';
    button.setAttribute('aria-label', 'Table');
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-haspopup', 'dialog');
    const tableIcon = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><rect x="2" y="3" width="16" height="14" rx="1" stroke="currentColor" stroke-width="1.4"/><path d="M2 7h16M2 12h16M7 7v10M12 7v10" stroke="currentColor" stroke-width="1.2"/><path d="M3 4h14v2H3z" fill="currentColor" opacity=".25"/></svg>';
    button.innerHTML = tableIcon + '<svg class="document-table-chevron" viewBox="0 0 10 10" aria-hidden="true"><path d="m2 4 3 3 3-3" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>';
    const panel = document.createElement('div');
    panel.className = 'document-table-panel';
    panel.setAttribute('popover', 'auto');
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Table options');
    const label = document.createElement('div');
    label.className = 'document-table-size';
    label.textContent = 'Insert Table';
    const grid = document.createElement('div');
    grid.className = 'document-table-grid';
    grid.setAttribute('role', 'group');
    grid.setAttribute('aria-label', 'Choose table size');
    const cells = [];
    const highlight = (rows, columns) => {
        label.textContent = `${columns} columns Ãƒâ€” ${rows} rows`;
        cells.forEach(cell => cell.classList.toggle('is-selected', Number(cell.dataset.row) <= rows && Number(cell.dataset.column) <= columns));
    };
    for (let row = 1; row <= 8; row++) {
        for (let column = 1; column <= 10; column++) {
            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'document-table-cell';
            cell.dataset.row = row;
            cell.dataset.column = column;
            cell.tabIndex = row === 1 && column === 1 ? 0 : -1;
            cell.setAttribute('aria-label', `${column} columns, ${row} rows`);
            cell.addEventListener('pointerenter', () => highlight(row, column));
            cell.addEventListener('focus', () => highlight(row, column));
            cell.addEventListener('click', () => {
                panel.hidePopover();
                editor.setSelection(selection.index, selection.length, 'silent');
                table.insertTable(row, column);
            });
            cell.addEventListener('keydown', event => {
                const offsets = { ArrowRight: 1, ArrowLeft: -1, ArrowDown: 10, ArrowUp: -10 };
                if (!(event.key in offsets)) return;
                event.preventDefault();
                const next = cells.indexOf(cell) + offsets[event.key];
                if (next >= 0 && next < cells.length) {
                    cell.tabIndex = -1;
                    cells[next].tabIndex = 0;
                    cells[next].focus();
                }
            });
            grid.append(cell);
            cells.push(cell);
        }
    }
    panel.append(label, grid);
    const actions = document.createElement('div');
    actions.className = 'document-table-actions';
    for (const [action, text] of [
        ['insert', 'Insert tableÃ¢â‚¬Â¦'],
        ['insertRowAbove', 'Insert row above'],
        ['insertRowBelow', 'Insert row below'],
        ['insertColumnLeft', 'Insert column left'],
        ['insertColumnRight', 'Insert column right'],
        ['selectRow', 'Select row'],
        ['selectColumn', 'Select column'],
        ['selectTable', 'Select table'],
        ['copyCells', 'Copy selected cells'],
        ['cutCells', 'Cut selected cells'],
        ['clearCells', 'Clear cell contents'],
        ['mergeCells', 'Merge selected cells'],
        ['unmergeCells', 'Unmerge cells'],
        ['deleteRow', 'Delete row'],
        ['deleteColumn', 'Delete column'],
        ['deleteTable', 'Delete table'],
    ]) {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'document-table-action';
        item.dataset.action = action;
        item.innerHTML = tableIcon;
        const textNode = document.createElement('span');
        textNode.textContent = text;
        item.append(textNode);
        item.addEventListener('click', () => runAction(action));
        actions.append(item);
    }
    panel.append(actions);
    controls.append(button, panel);
    editor.getModule('toolbar').container.append(controls);
    let selection = { index: 0, length: 0 };
    button.addEventListener('click', () => {
        if (panel.matches(':popover-open')) {
            panel.hidePopover();
            return;
        }
        editor.update('user');
        selection = editor.getSelection(true) ?? selection;
        const inTable = Boolean(table.getTable(selection)[0]);
        label.textContent = inTable ? 'Table options' : 'Insert Table';
        cells.forEach(cell => { cell.disabled = inTable; cell.classList.remove('is-selected'); });
        grid.hidden = inTable;
        actions.querySelectorAll('button').forEach(item => { item.disabled = item.dataset.action === 'insert' ? inTable : !inTable; });
        actions.querySelector('[data-action="mergeCells"]').disabled = !inTable || !selectedCells(editor)?.rectangular || !(selectedCells(editor)?.cells.length > 1);
        actions.querySelector('[data-action="unmergeCells"]').disabled = !inTable || !selectedCells(editor)?.cells.some(cell => cell.colSpan > 1 || cell.rowSpan > 1);
        actions.querySelector('[data-action="deleteRow"] span').textContent = selectedCells(editor)?.explicit ? 'Delete selected rows' : 'Delete row';
        actions.querySelector('[data-action="deleteColumn"] span').textContent = selectedCells(editor)?.explicit ? 'Delete selected columns' : 'Delete column';
        panel.showPopover();
        const rect = button.getBoundingClientRect();
        panel.style.left = `${Math.max(8, Math.min(rect.left, window.innerWidth - panel.offsetWidth - 8))}px`;
        panel.style.top = `${Math.max(8, Math.min(rect.bottom + 4, window.innerHeight - panel.offsetHeight - 8))}px`;
    });
    panel.addEventListener('toggle', event => button.setAttribute('aria-expanded', String(event.newState === 'open')));

    async function runAction(action) {
        panel.hidePopover();
        editor.update('user');
        const range = selection;
        if (!range) return;
        editor.setSelection(range.index, range.length, 'silent');

        if (action.startsWith('select')) {
            selectTablePart(editor, action.slice(6).toLowerCase());
            return;
        }
        if (action === 'clearCells') { clearSelectedContents(editor); return; }
        if (action === 'copyCells' || action === 'cutCells') {
            if (!await copySelectedCells(editor, action === 'cutCells')) {
                await Swal.fire({ icon: 'info', text: 'Use Ctrl+C to copy or Ctrl+X to cut the highlighted cells.' });
            }
            return;
        }
        if (action === 'mergeCells') { mergeCells(editor); return; }
        if (action === 'unmergeCells') { unmergeCells(editor); return; }

        if (action === 'insert') {
            if (table.getTable()[0]) {
                await Swal.fire({ icon: 'info', text: 'Click outside the current table to insert another table.' });
                return;
            }
            const result = await Swal.fire({
                title: 'Insert table',
                html: '<label for="document-table-rows">Rows (1ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“20)</label>' +
                    '<input id="document-table-rows" class="swal2-input" type="number" min="1" max="20" step="1" value="3">' +
                    '<label for="document-table-columns">Columns (1ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“10)</label>' +
                    '<input id="document-table-columns" class="swal2-input" type="number" min="1" max="10" step="1" value="3">',
                showCancelButton: true,
                confirmButtonText: 'Insert table',
                focusConfirm: false,
                didOpen: () => document.getElementById('document-table-rows').focus(),
                preConfirm: () => {
                    const rows = Number(document.getElementById('document-table-rows').value);
                    const columns = Number(document.getElementById('document-table-columns').value);
                    if (!Number.isInteger(rows) || rows < 1 || rows > 20 ||
                        !Number.isInteger(columns) || columns < 1 || columns > 10) {
                        Swal.showValidationMessage('Enter 1ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“20 rows and 1ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“10 columns.');
                        return false;
                    }
                    return { rows, columns };
                },
            });
            if (!editor.root.isConnected) return;
            editor.setSelection(range.index, range.length, 'silent');
            if (result.isConfirmed) table.insertTable(result.value.rows, result.value.columns);
        } else if (table.getTable()[0]) {
            if (action !== 'deleteTable') {
                editTableStructure(editor, action, range);
            } else {
                clearCellSelection(editor);
                table.deleteTable();
            }
        } else {
            await Swal.fire({ icon: 'info', text: 'Click inside a table cell first, then choose a table option.' });
        }
    }
}
