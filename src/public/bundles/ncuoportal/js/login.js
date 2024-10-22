$(document).ready(function() {
    $('#sh_login').click(function() {
        var icon = $(this).find('i');
        
        if (icon.hasClass('fa-caret-up')) {
            icon.removeClass('fa-caret-up').addClass('fa-caret-down');
            $('#login-form').slideDown();
        } else {
            icon.removeClass('fa-caret-down').addClass('fa-caret-up');
            $('#login-form').slideUp();
        }        
    });    
});