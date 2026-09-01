import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';

let editor = null;

function mount() {
    const el = document.getElementById('post-body');
    if (! el || editor) {
        return;
    }

    editor = new EasyMDE({
        element: el,
        spellChecker: false,
        autoDownloadFontAwesome: true,
        status: ['lines', 'words'],
        minHeight: '400px',
    });

    window.postEditor = editor;
}

function unmount() {
    if (editor) {
        editor.toTextArea();
        editor = null;
        window.postEditor = null;
    }
}

document.addEventListener('DOMContentLoaded', mount);
document.addEventListener('livewire:navigating', unmount);
document.addEventListener('livewire:navigated', mount);
