( function ($) {
    $.extend($.summernote.plugins, {
        /**
         * @param {Object} context - context object has status of editor.
         */
        'zikulapagesmodule': function (context) {
            var self = this;

            // ui provides methods to build ui elements.
            var ui = $.summernote.ui;

            // add button
            context.memo('button.zikulapagesmodule', function () {
                // create button
                var button = ui.button({
                    contents: '<img src="' + Zikula.Config.baseURL + Zikula.Config.baseURI + '/public/modules/zikulapages/images/admin.png' + '" alt="Pages" width="16" height="16" />',
                    tooltip: 'Pages',
                    click: function () {
                        ZikulaPagesModuleFinderOpenPopup(context, 'summernote');
                    }
                });

                // create jQuery object from button instance.
                var $button = button.render();

                return $button;
            });
        }
    });
})(jQuery);
