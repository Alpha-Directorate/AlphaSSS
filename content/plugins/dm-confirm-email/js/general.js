jQuery(document).ready(function($) {
    $(".nav-tab").click(function(e){
        e.preventDefault();

        // Get clicked id
        var elementId = '#' + $(this).attr('id') + '-div';

        // Hide all div
        $(".dmec-div").css('display', 'none');

        // Display the selected div
        $(elementId).css('display', 'inline');

        // Remove all the active nav
        $(".nav-tab").removeClass('nav-tab-active');

        // Active the nav
        $(this).addClass('nav-tab-active');
    });
});