export function registerCellBreak(Quill) {
    const Embed = Quill.import('blots/embed');
    class CellBreak extends Embed {
        static blotName = 'cell-break';
        static tagName = 'SPAN';
        static className = 'document-cell-break';
        static create() {
            const node = super.create();
            node.appendChild(document.createElement('br'));
            return node;
        }
        static value() { return true; }
    }
    Quill.register(CellBreak);
}

export const cellEnterBinding = {
    key: 'Enter',
    shiftKey: null,
    format: ['table'],
    handler(range) {
        const [cell, offset] = this.quill.getLine(range.index);
        const length = Math.min(range.length, cell.length() - offset - 1);
        const Delta = this.quill.constructor.import('delta');
        this.quill.updateContents(new Delta().retain(range.index).delete(length).insert({ 'cell-break': true }), 'user');
        this.quill.setSelection(range.index + 1, 0, 'silent');
        return false;
    },
};
