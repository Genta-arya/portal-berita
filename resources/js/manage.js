import './avatar.js';
import {Jodit} from 'jodit';
import 'jodit/esm/plugins/source/source.js';
import 'jodit/es2021/jodit.min.css';

const form = document.getElementById('management-form');
const dialog = document.getElementById('management-dialog');
let pageEditor;
if (form && dialog) {
    const heading = document.getElementById('management-title');
    const body = form.querySelector('[data-page-editor]');
    const open = () => {
        if (!dialog.open) dialog.showModal();
        if (body && !pageEditor) {
            pageEditor = Jodit.make(body, {
                height: 350, minHeight: 280, toolbarAdaptive: false, toolbarSticky: false,
                sourceEditor: 'area', beautifyHTML: false,
                buttons: ['undo','redo','|','paragraph','bold','italic','|','ul','ol','|','link','eraser'],
                controls: {paragraph:{list:{p:'Paragraf',h2:'Judul bagian',h3:'Subjudul',blockquote:'Kutipan'}}},
                showCharsCounter:false, showWordsCounter:false, showXPathInStatusbar:false,
                disablePlugins:['file','filebrowser','image','video','drag-and-drop','drag-and-drop-element']
            });
            pageEditor.events.on('change', () => { body.value = pageEditor.value; });
            const modeButtons = form.querySelectorAll('[data-page-mode]');
            const syncMode = () => {
                const source = pageEditor.getMode() === Jodit.MODE_SOURCE;
                modeButtons.forEach(button => button.setAttribute('aria-pressed', String((button.dataset.pageMode === 'html') === source)));
            };
            modeButtons.forEach(button => button.addEventListener('click', () => {
                pageEditor.setMode(button.dataset.pageMode === 'html' ? Jodit.MODE_SOURCE : Jodit.MODE_WYSIWYG);
                syncMode();
            }));
            pageEditor.events.on('afterSetMode', syncMode);
            syncMode();
            // Jodit is the accessible editor; browser validation targets its text content below.
            body.required = false;
        }
        form.querySelector('input:not([type=hidden])')?.focus();
    };
    document.querySelectorAll('[data-create-row]').forEach(button => button.addEventListener('click', () => {
        form.reset();
        form.elements.namedItem('id').value = '';
        form.querySelector('[data-avatar-upload]')?.dispatchEvent(new CustomEvent('avatar:reset', {detail:{url:''}}));
        if (pageEditor) { pageEditor.setMode(Jodit.MODE_WYSIWYG); pageEditor.value = ''; }
        heading.textContent = heading.dataset.createTitle;
        const password = form.elements.namedItem('password');
        if (password) { password.required = true; form.elements.namedItem('password_confirmation').required = true; }
        open();
    }));
    document.querySelectorAll('[data-edit-row]').forEach(button => button.addEventListener('click', () => {
        form.reset();
        const data = JSON.parse(button.dataset.editRow);
        form.querySelector('[data-avatar-upload]')?.dispatchEvent(new CustomEvent('avatar:reset', {detail:{url:data.avatar_url}}));
        for (const [key,value] of Object.entries(data)) {
            const field = form.elements.namedItem(key);
            if (!field || field.type === 'password') continue;
            let next = value ?? '';
            if (['starts_at','ends_at'].includes(key) && next) {
                const utc = new Date(String(next).replace(' ','T') + (/Z$|[+]\d\d:\d\d$/.test(String(next)) ? '' : 'Z'));
                next = new Intl.DateTimeFormat('sv-SE',{timeZone:'Asia/Jakarta',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit'}).format(utc).replace(' ','T');
            }
            field.value = String(next);
        }
        heading.textContent = 'Edit ' + (data.title || data.name || data.text || data.alt || heading.dataset.createTitle.replace(/^Tambah /, ''));
        const password = form.elements.namedItem('password');
        if (password) { password.value = ''; password.required = false; const confirmation = form.elements.namedItem('password_confirmation'); confirmation.value = ''; confirmation.required = false; }
        if (pageEditor) { pageEditor.setMode(Jodit.MODE_WYSIWYG); pageEditor.value = data.body ?? ''; }
        open();
    }));
    document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', event => { if (event.target === dialog && event.clientX < dialog.getBoundingClientRect().left) dialog.close(); });
    const accountPassword = form.elements.namedItem('password');
    if (accountPassword) accountPassword.addEventListener('input', () => {
        form.elements.namedItem('password_confirmation').required = !!accountPassword.value || !form.elements.namedItem('id').value;
    });
    dialog.addEventListener('close', () => {
        form.querySelectorAll('[data-reveal-password]').forEach(button => {
            document.getElementById(button.dataset.revealPassword).type = 'password';
            button.setAttribute('aria-pressed','false');
            button.setAttribute('aria-label','Tampilkan kata sandi');
        });
        if (accountPassword) { accountPassword.value = ''; form.elements.namedItem('password_confirmation').value = ''; }
    });
    form.addEventListener('submit', event => {
        if (pageEditor) {
            // Switching modes flushes source edits before reading the value, including rapid submits.
            if (pageEditor.getMode() === Jodit.MODE_SOURCE) pageEditor.setMode(Jodit.MODE_WYSIWYG);
            body.value = pageEditor.value;
            const text = new DOMParser().parseFromString(body.value, 'text/html').body.textContent;
            if (!text.trim()) { event.preventDefault(); pageEditor.setMode(Jodit.MODE_WYSIWYG); pageEditor.editor.focus(); }
        }
    });
    if (dialog.dataset.openOnError === 'true') open();
    const slug = form.elements.namedItem('slug');
    if (slug) slug.addEventListener('input', () => { form.querySelector('[data-slug-preview]').textContent = slug.value || 'contoh-alamat'; });
}
document.querySelectorAll('[data-confirm-delete]').forEach(form => form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirmDelete)) event.preventDefault();
}));

document.querySelectorAll('[data-reveal-password]').forEach(button => button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.revealPassword);
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    button.setAttribute('aria-pressed', String(visible));
    button.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
}));
