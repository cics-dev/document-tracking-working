import '../css/app.css'; // Tailwind CSS
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

let quill;

function initQuillEditor() {
    const editorElement = document.getElementById('quill-editor');

    if (editorElement && !editorElement.classList.contains('ql-container')) {
        const quill = new Quill(editorElement, {
            modules: {
                toolbar: [
                    [{ 'align': [] }, { 'color': [] }],
                    ['bold', 'italic', 'underline', 'strike', { script: 'super' }, { script: 'sub' }],
                    ['image', 'code-block', 'blockquote'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                ],
            },
            placeholder: 'Compose an epic...',
            theme: 'snow',
        });

        // Optional: Livewire sync
        quill.on('text-change', function () {
            Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'))?.set('content', quill.root.innerHTML);
        });
    }
}

document.addEventListener('submitDocument', function (e) {
    const contentInput = document.getElementById('quill-content');
    if (contentInput && quill) {
        contentInput.value = quill.root.innerHTML;
    }
});

document.addEventListener('DOMContentLoaded', initQuillEditor);
document.addEventListener('livewire:navigated', initQuillEditor);
