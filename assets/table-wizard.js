var RteTableWizard = {
    edit: function (options) {
        var opt = options || {},
            max = (window.getSize().y - 180).toInt();
        if (!opt.height || opt.height > max) opt.height = max;
        var M = new SimpleModal({
            'width': opt.width,
            'btn_ok': Contao.lang.close,
            'draggable': false,
            'overlayOpacity': .5,
            'onShow': function () {
                document.body.setStyle('overflow', 'hidden');
            },
            'onHide': function () {
                document.body.setStyle('overflow', 'auto');
            },
            'onAppend': function () {
                var frm = window.frames['simple-modal-iframe'];

                if (frm === undefined) {
                    alert('Could not find the SimpleModal frame');
                    return;
                }

                frm.onload = function () {
                    var content = opt.el.getNext('.rte-content').get('html');

                    frm.document.getElementById('rte-table-editor').set('value', content);

                    setTimeout(function () {
                        frm.tinyMCE.activeEditor.setContent(content);
                    }, 100);

                    M.buttons.forEach(function(button) {
                        button.classList.remove('btn-disabled');
                    });
                };
            }
        });
        M.addButton(Contao.lang.apply, 'btn primary', function () {
            var frm = window.frames['simple-modal-iframe'];

            if (frm === undefined) {
                alert('Could not find the SimpleModal frame');
                return;
            }

            var content = frm.tinyMCE.activeEditor.getContent();

            // Set the content to textarea
            opt.el.getNext('textarea').set('value', content);

            // Update the content value
            opt.el.getNext('.rte-content').set('html', content);

            this.hide();
        });
        M.show({
            'title': opt.title,
            'contents': '<iframe src="' + opt.url + '" name="simple-modal-iframe" width="100%" height="' + opt.height + '" frameborder="0"></iframe>'
        });
    }
};
