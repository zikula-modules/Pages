/**
 * Initializes the plugin, this will be executed after the plugin has been created.
 * This call is done before the editor instance has finished it's initialization so use the onInit event
 * of the editor instance to intercept that event.
 *
 * @param {tinymce.Editor} ed Editor instance that the plugin is initialised in
 * @param {string} url Absolute URL to where the plugin is located
 */
tinymce.PluginManager.add('zikulapagesmodule', function(editor, url) {
    var icon;

    icon = Zikula.Config.baseURL + Zikula.Config.baseURI + '/public/modules/zikulapages/images/admin.png';

    editor.addButton('zikulapagesmodule', {
        //text: 'Pages',
        image: icon,
        onclick: function() {
            ZikulaPagesModuleFinderOpenPopup(editor, 'tinymce');
        }
    });
    editor.addMenuItem('zikulapagesmodule', {
        text: 'Pages',
        context: 'tools',
        image: icon,
        onclick: function() {
            ZikulaPagesModuleFinderOpenPopup(editor, 'tinymce');
        }
    });

    return {
        getMetadata: function() {
            return {
                title: 'Pages',
                url: 'https://ziku.la'
            };
        }
    };
});
