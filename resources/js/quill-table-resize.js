import { registerMergeFormats, columnWidths, applyCellChanges } from './quill-table-cells';

export function registerTableColumnWidths(Quill) {
    const { Attributor, Scope } = Quill.import('parchment');
    class ColumnWidth extends Attributor {
        canAdd(node, value) {
            return node.tagName === 'TD' && /^\d+(\.\d+)?%$/.test(value) &&
                Number.parseFloat(value) > 0 && Number.parseFloat(value) <= 100 && super.canAdd(node, value);
        }
    }
    Quill.register(new ColumnWidth('column-width', 'width', { scope: Scope.BLOCK }));
    registerMergeFormats(Quill);
}

export function addTableColumnResize(editor) {
    const root = editor.root, container = editor.container;
    const handle = document.createElement('div');
    handle.className = 'document-table-resize-handle';
    handle.hidden = true;
    handle.setAttribute('aria-hidden', 'true');
    container.append(handle);
    let drag = null, hovered = null;

    function borderAt(event) {
        const header = event.target.closest('.document-table-header');
        if (!header && event.target.closest('.document-table-headers')) return null;
        if (header) {
            const table = header.documentTable, rect = header.getBoundingClientRect();
            const index = Number(header.dataset.headerIndex), part = header.dataset.headerPart;
            const tableRect = table.getBoundingClientRect();
            if (part === 'column') {
                const right = Math.abs(event.clientX - rect.right) <= 5;
                const left = Math.abs(event.clientX - rect.left) <= 5;
                if (right || left) {
                    const column = right ? index : index - 1;
                    return { table, column, edge: column < 0 ? 'left' : column === table.rows[0].cells.length - 1 ? 'right' : undefined, x: right ? rect.right : rect.left };
                }
                if (Math.abs(event.clientY - tableRect.top) <= 9) return { table, vertical: true, row: 0, top: true, y: tableRect.top };
            } else if (part === 'row') {
                if (Math.abs(event.clientX - tableRect.left) <= 6.5) return { table, edge: 'left', x: tableRect.left };
                const bottom = Math.abs(event.clientY - rect.bottom) <= 4;
                const top = Math.abs(event.clientY - rect.top) <= 4;
                if (bottom || top) return { table, vertical: true, row: bottom ? index : Math.max(0, index - 1), top: top && index === 0, y: bottom ? rect.bottom : rect.top };
            }
            return null;
        }
        for (const table of root.querySelectorAll('table')) {
            const rect = table.getBoundingClientRect();
            if (event.clientY < rect.top - 10 || event.clientY > rect.bottom + 10) continue;
            if (event.clientX >= rect.left + 12 && event.clientX <= rect.right - 12) {
                if (Math.abs(event.clientY - rect.top) <= 9) return { table, vertical: true, row: 0, top: true, y: rect.top };
                if (Math.abs(event.clientY - rect.bottom) <= 9) return { table, vertical: true, row: table.rows.length - 1, y: rect.bottom };
                for (let r = 0; r < table.rows.length - 1; r++) {
                    const bottom = table.rows[r].getBoundingClientRect().bottom;
                    if (Math.abs(event.clientY - bottom) <= 6) return { table, vertical: true, row: r, y: bottom };
                }
            }
            if (Math.abs(event.clientX - rect.left) <= 12) return { table, edge: 'left', x: rect.left };
            if (Math.abs(event.clientX - rect.right) <= 12) return { table, edge: 'right', x: rect.right };
            for (const row of table.rows) {
                for (const cell of row.cells) {
                    if (cell.hasAttribute('data-cell-covered')) continue;
                    const bounds = cell.getBoundingClientRect();
                    if (event.clientY >= bounds.top && event.clientY <= bounds.bottom && Math.abs(event.clientX - bounds.right) <= 10 && bounds.right < rect.right - 2) {
                        return { table, column: cell.cellIndex + cell.colSpan - 1, x: bounds.right };
                    }
                }
            }
        }
        return null;
    }
    function show(border) {
        hovered = border;
        handle.hidden = !border;
        root.style.cursor = border ? (border.vertical ? 'row-resize' : 'col-resize') : '';
        handle.classList.toggle('is-horizontal', Boolean(border?.vertical));
        if (!border) return;
        const rect = border.table.getBoundingClientRect(), parent = container.getBoundingClientRect();
        handle.style.left = `${(border.vertical ? rect.left : border.x) - parent.left}px`;
        handle.style.top = `${(border.vertical ? border.y : rect.top) - parent.top}px`;
        handle.style.height = border.vertical ? '18px' : `${rect.height}px`;
        handle.style.width = border.vertical ? `${rect.width}px` : '20px';
    }
    container.addEventListener('pointermove', event => {
        if (!drag) {
            const border = borderAt(event);
            const header = event.target.closest('.document-table-header');
            if (header) header.style.cursor = border ? border.vertical ? 'row-resize' : 'col-resize' : '';
            show(border);
            return;
        }
        if (event.pointerId !== drag.pointerId) return;
        const { column, widths, tableWidth, table, edge } = drag;
        const delta = event.clientX - drag.startX;
        if (drag.vertical) {
            drag.heights = [...drag.startHeights];
            const change = (event.clientY - drag.startY) * (drag.top ? -1 : 1);
            drag.heights[drag.row] = Math.round(Math.max(24, Math.min(2000, drag.startHeights[drag.row] + change)));
            Array.from(table.rows).forEach((row, r) => {
                row.style.height = `${drag.heights[r]}px`;
                Array.from(row.cells).forEach(cell => {
                    cell.style.height = `${drag.heights.slice(r, r + cell.rowSpan).reduce((a, b) => a + b, 0)}px`;
                });
            });
            const rect = table.rows[drag.row].getBoundingClientRect();
            show({ table, vertical: true, y: drag.top ? rect.top : rect.bottom });
        } else if (edge) {
            const minimum = Math.min(drag.available, widths.length * 32);
            const left = edge === 'left' ? Math.max(0, Math.min(drag.startLeft + tableWidth - minimum, drag.startLeft + delta)) : drag.startLeft;
            const width = edge === 'left' ? drag.startLeft + tableWidth - left : Math.max(minimum, Math.min(drag.available - left, tableWidth + delta));
            drag.size = { width: width / drag.available * 100, offset: left / drag.available * 100 };
            table.style.width = `${drag.size.width}%`;
            table.style.marginLeft = `${drag.size.offset}%`;
            show({ table, x: edge === 'left' ? table.getBoundingClientRect().left : table.getBoundingClientRect().right });
        } else {
            const minimum = 32 / tableWidth * 100, combined = widths[column] + widths[column + 1];
            const width = widths[column] + delta / tableWidth * 100;
            drag.preview = [...widths];
            drag.preview[column] = Math.max(minimum, Math.min(combined - minimum, width));
            drag.preview[column + 1] = combined - drag.preview[column];
            Array.from(table.rows).forEach(row => Array.from(row.cells).forEach((cell, c) => {
                cell.style.width = `${drag.preview.slice(c, c + cell.colSpan).reduce((a, b) => a + b, 0)}%`;
            }));
            show({ table, x: drag.startX + (drag.preview[column] - widths[column]) / 100 * tableWidth });
        }
    });
    container.addEventListener('pointerdown', event => {
        if (event.button !== 0) return;
        const border = event.target === handle ? hovered : borderAt(event);
        if (!border) return;
        const rect = border.table.getBoundingClientRect(), rootRect = root.getBoundingClientRect(), css = getComputedStyle(root);
        const available = root.clientWidth - parseFloat(css.paddingLeft) - parseFloat(css.paddingRight);
        const widths = columnWidths(border.table);
        if (!border.vertical && !border.edge && (widths[border.column] + widths[border.column + 1]) * rect.width / 100 < 64) return;
        event.preventDefault(); event.stopPropagation();
        drag = { ...border, widths, tableWidth: rect.width, available, startLeft: rect.left - rootRect.left - parseFloat(css.paddingLeft), startX: event.clientX, pointerId: event.pointerId, oldWidth: border.table.style.width, oldOffset: border.table.style.marginLeft };
        drag.startY = event.clientY;
        drag.startHeights = Array.from(border.table.rows, row => Math.max(24, Math.round(row.getBoundingClientRect().height)));
        container.setPointerCapture(event.pointerId);
    }, true);
    function finish(event, cancelled = false) {
        if (!drag || event.pointerId !== drag.pointerId) return;
        const { table, preview: widths, pointerId, size, oldWidth, oldOffset, heights } = drag;
        Array.from(table.rows).forEach(row => Array.from(row.cells).forEach(cell => { cell.style.removeProperty('width'); cell.style.height = cell.hasAttribute('height') ? `${cell.getAttribute('height')}px` : ''; }));
        table.style.width = oldWidth; table.style.marginLeft = oldOffset;
        const storedHeights = table.querySelector('td').getAttribute('data-table-row-heights')?.split(',');
        Array.from(table.rows).forEach((row, r) => { row.style.height = storedHeights?.[r] ? `${storedHeights[r]}px` : ''; });
        drag = null;
        show(null);
        if (container.hasPointerCapture(pointerId)) container.releasePointerCapture(pointerId);
        if (cancelled || !table.isConnected) return;
        if (heights) {
            applyCellChanges(editor, table, () => ({ 'table-row-heights': heights.join(',') }));
        } else if (size) {
            applyCellChanges(editor, table, () => ({ 'table-width': `${size.width.toFixed(4)}%`, 'table-offset': `${size.offset.toFixed(4)}%` }));
        } else if (widths) {
            applyCellChanges(editor, table, (cell, r, c) => ({
                'column-width': `${widths.slice(c, c + cell.colSpan).reduce((a, b) => a + b, 0).toFixed(4)}%`,
                'cell-merge-widths': cell.colSpan > 1 || cell.rowSpan > 1 ? widths.slice(c, c + cell.colSpan).map(width => `${width.toFixed(4)}%`).join(',') : null,
                'cell-original-width': cell.colSpan > 1 ? `${widths[c].toFixed(4)}%` : null,
            }));
        }
    }
    container.addEventListener('pointerup', event => finish(event));
    container.addEventListener('pointercancel', event => finish(event, true));
    container.addEventListener('lostpointercapture', event => finish(event, true));
    container.addEventListener('pointerleave', () => { if (!drag) show(null); });
    root.addEventListener('scroll', () => { if (!drag) show(null); });
}
