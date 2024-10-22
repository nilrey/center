$( document ).ready(function() {
    $(document).tooltip();
    var navItem = "dashboard";
    var listItem = $('#' + navItem);
    listItem.addClass("active");
    if (listItem.parent('.nav').hasClass("nav-n-level")) {
        listItem.parents('.nav').addClass('in').parent('li').addClass("active");
    }
    
    
});

function wideScreenView() {
    $('#page-wrapper').toggleClass("wide-page-wrapper");
    $('.menu-left-block').toggleClass("wide-menu-left-block");
    $('body').toggleClass("wide-body");
    $('.ico-resize').toggleClass("ico-resize-wide");
}
