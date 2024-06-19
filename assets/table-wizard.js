import './table-wizard.scss';

const initializedCells = new WeakMap();

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

    content.className = 'rte-content'
    content.innerHTML = textarea.value;

    cell.prepend(link, content);
    cell.querySelector('textarea').style.display = 'none';
};

const click = (contentEl, textareaEl) => {
    const url = textareaEl.closest('#tl_tablewizard').dataset.rteUrl;
    const M = new SimpleModal({
        width: '80vw',
        draggable: false,
        overlayOpacity: .5,
        onShow: function () {
            document.body.style.overflow = 'hidden';
        },
        onHide: function () {
            document.body.style.overflow = '';
        },
        onAppend: function () {
            const frm = window.frames['simple-modal-iframe'];

            if (frm === undefined) {
                alert('Could not find the SimpleModal frame');
                return;
            }

            frm.onload = function () {
                frm.document.getElementById('rte-table-editor').value = contentEl.innerHTML;

                setTimeout(function () {
                    frm.tinyMCE.activeEditor.setContent(contentEl.innerHTML);
                }, 100);

                M.buttons.forEach(function(button) {
                    button.classList.remove('btn-disabled');
                });
            };
        }
    });
    M.addButton(Contao.lang.cancel, 'btn', function () {
        this.hide();
    });
    M.addButton(Contao.lang.apply, 'btn primary', function () {
        const frm = window.frames['simple-modal-iframe'];

        if (frm === undefined) {
            alert('Could not find the SimpleModal frame');
            return;
        }

        const content = frm.tinyMCE.activeEditor.getContent();

        textareaEl.value = content;
        contentEl.innerHTML = content;

        this.hide();
    });
    M.show({
        'model': 'modal',
        'title': textareaEl.closest('#tl_tablewizard').dataset.rteLabel,
        'contents': `<iframe src="${url}" name="simple-modal-iframe" width="100%" height="${window.innerHeight * 0.8}" frameborder="0"></iframe>`
    });
};

document.querySelectorAll('#tl_tablewizard .tcontainer').forEach(init);

new MutationObserver(function(mutationsList) {
    for (const mutation of mutationsList) {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(function(element) {
                console.log(element);
                if (element.matches && element.matches('#tl_tablewizard .tcontainer')) {
                    init(element);
                } else if (element.matches && element.matches('#tl_tablewizard *')) {
                    element.querySelectorAll('.tcontainer').forEach((el) => {
                        init(el);
                    })
                }
            })
        }
    }
}).observe(document, {
    attributes: false,
    childList: true,
    subtree: true
});
