var zikulapagesmodule = function(quill, options) {
    setTimeout(function() {
        var button;

        button = jQuery('button[value=zikulapagesmodule]');

        button
            .css('background', 'url(' + Zikula.Config.baseURL + Zikula.Config.baseURI + '/public/modules/zikulapages/images/admin.png) no-repeat center center transparent')
            .css('background-size', '16px 16px')
            .attr('title', 'Pages')
        ;

        button.click(function() {
            ZikulaPagesModuleFinderOpenPopup(quill, 'quill');
        });
    }, 1000);
};
