( function($) {
    $(document).ready(function() {
        $('#pages_settings_collapse > i').removeClass('hide');
        $('#pages_meta_collapse i').removeClass('hide');

        $('#pages_settings_details').hide();
        $('#pages_meta_details').hide();

        $('#pages_settings_collapse').click(function(event) {
            event.preventDefault();
            $('#pages_settings_details').fadeToggle({duration: 1000});
        });

        $('#pages_meta_collapse').click(function(event) {
            event.preventDefault();
            $('#pages_meta_details').fadeToggle({duration: 1000});
        });
    });
})(jQuery);