import '../css/app.css'; // Tailwind CSS
import Quill from 'quill';
import Swal from 'sweetalert2';
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
                    [{ list: 'ordered' }],
                ],
            },
            placeholder: 'Compose an epic...',
            theme: 'snow',
        });

        const quillContentInput = document.getElementById('quill-content');

        if (quillContentInput.value) {
            quill.root.innerHTML = quillContentInput.value;
        }

        quill.on('text-change', function () {
            const content = quill.root.innerHTML;
            quillContentInput.value = content;
            quillContentInput.dispatchEvent(new Event('input')); // Trigger Livewire update
        });

        // // Optional: Livewire sync
        // quill.on('text-change', function () {
        //     Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'))?.set('content', quill.root.innerHTML);
        // });
    }
}

// document.addEventListener('submitDocument', function (e) {
//     const contentInput = document.getElementById('quill-content');
//     if (contentInput && quill) {
//         contentInput.value = quill.root.innerHTML;
//     }
// });

document.addEventListener('DOMContentLoaded', initQuillEditor);
document.addEventListener('livewire:navigated', initQuillEditor);

window.addEventListener('open-preview-tab', event => {
    const url = event.detail[0].url;
    window.open(url, '_blank'); // open in new tab
});

document.addEventListener('fireSwal', event => {
    const data = event.detail[0];
    Swal.fire(data).then((result) => {
        if (result.isConfirmed) {
            if (data.event === 'redirect')
                window.location.href = data.url;
            else if (data.text)
                Livewire.dispatch(data.event, { remarks: result.value });
            else
                if (data.withId) Livewire.dispatch(data.event, { id: data.id });
                else Livewire.dispatch(data.event);
        }
    })
});