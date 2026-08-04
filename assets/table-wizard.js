import './table-wizard.scss';

const initializedCells = new WeakMap();

const click = (contentEl, textareaEl) => {
    const url = textareaEl.closest('#tl_tablewizard').dataset.rteUrl;
    const M = new SimpleModal({
        width: '80vw',
        draggable: false,
        overlayOpacity: 0.5,
        onShow() {
            document.body.style.overflow = 'hidden';
        },
        onHide() {
            document.body.style.overflow = '';
        },
        onAppend() {
            const frm = window.frames['simple-modal-iframe'];

            if (frm === undefined) {
                throw new Error('Could not find the SimpleModal frame');
            }

            frm.onload = function () {
                frm.document.getElementById('rte-table-editor').value = contentEl.innerHTML;

                setTimeout(() => {
                    frm.tinyMCE.activeEditor.setContent(contentEl.innerHTML);
                }, 100);

                M.buttons.forEach((button) => {
                    button.classList.remove('btn-disabled');
                });
            };
        },
    });
    M.addButton(Contao.lang.cancel, 'btn', function () {
        this.hide();
    });
    M.addButton(Contao.lang.apply, 'btn primary', function () {
        const frm = window.frames['simple-modal-iframe'];

        if (frm === undefined) {
            throw new Error('Could not find the SimpleModal frame');
        }

        const content = frm.tinyMCE.activeEditor.getContent();

        textareaEl.value = content;
        contentEl.innerHTML = content;

        this.hide();
    });
    M.show({
        model: 'modal',
        title: textareaEl.closest('#tl_tablewizard').dataset.rteLabel,
        contents: `<iframe src="${url}" name="simple-modal-iframe" width="100%" height="${window.innerHeight * 0.8}" frameborder="0"></iframe>`,
    });
};

const init = (cell) => {
    // Check if this cell has already been initialized
    if (initializedCells.has(cell)) {
        return;
    }

    initializedCells.set(cell, true);

    cell.closest('table').classList.add('rte-table-wizard');

    const link = cell.querySelector('button') || document.createElement('button');
    const content = cell.querySelector('div') || document.createElement('div');
    const textarea = cell.querySelector('textarea');

    link.type = 'button';
    link.className = 'tl_submit rte-edit';
    link.innerText = cell.closest('#tl_tablewizard').dataset.rteLabel;
    link.addEventListener('click', () => click(content, textarea));

    content.className = 'rte-content';
    content.innerHTML = textarea.value;

    // The textarea is hidden, so these events cannot come from the user typing - they are
    // dispatched by other scripts writing to the cell (e.g. a machine translation button).
    // Without re-rendering, such a value would be submitted but stay invisible to the editor.
    ['input', 'change'].forEach((eventName) => {
        textarea.addEventListener(eventName, () => {
            content.innerHTML = textarea.value;
        });
    });

    cell.prepend(link, content);
    cell.querySelector('textarea').style.display = 'none';
};

document.querySelectorAll('#tl_tablewizard .tcontainer').forEach(init);

window.addEventListener('turbo:render', () => {
    document.querySelectorAll('#tl_tablewizard .tcontainer').forEach(init);
});

new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach((element) => {
                if (element.matches && element.matches('#tl_tablewizard .tcontainer')) {
                    init(element);
                } else if (element.matches && element.matches('#tl_tablewizard *')) {
                    element.querySelectorAll('.tcontainer').forEach((el) => {
                        init(el);
                    });
                }
            });
        }
    }
}).observe(document, {
    attributes: false,
    childList: true,
    subtree: true,
});
