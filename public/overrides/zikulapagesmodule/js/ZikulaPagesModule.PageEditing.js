'use strict';

jQuery(document).ready(function () {
    if (jQuery('#pageSettingsFieldsSection').length > 0) {
        jQuery('#pageSettingsFieldsContent').addClass('d-none');
        jQuery('#pageSettingsFieldsSection legend').css({cursor: 'pointer'}).click(function (event) {
            if (jQuery('#pageSettingsFieldsContent').hasClass('d-none')) {
                jQuery('#pageSettingsFieldsContent').removeClass('d-none');
                jQuery(this).find('i').removeClass('fa-expand').addClass('fa-compress');
            } else {
                jQuery('#pageSettingsFieldsContent').addClass('d-none');
                jQuery(this).find('i').removeClass('fa-compress').addClass('fa-expand');
            }
        });
    }
});
