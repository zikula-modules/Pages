CKEDITOR.plugins.add('zikulapagesmodule', {
    requires: 'popup',
    init: function (editor) {
        editor.addCommand('insertZikulaPagesModule', {
            exec: function (editor) {
                ZikulaPagesModuleFinderOpenPopup(editor, 'ckeditor');
            }
        });
        editor.ui.addButton('zikulapagesmodule', {
            label: 'Pages',
            command: 'insertZikulaPagesModule',
            icon: this.path.replace('scribite/CKEditor/zikulapagesmodule', 'images') + 'admin.png'
        });
    }
});
