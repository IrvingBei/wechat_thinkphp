// JavaScript Document

$(function(){

    $("#side-menu").metisMenu();
    
    $(".sidebar-collapse").slimScroll({ height: "100%", railOpacity: .9, alwaysVisible: !1 });
    
    $(".navbar-minimalize").click(function () {
        $("body").toggleClass("mini-navbar"), SmoothlyMenu()
    });

    $("#side-menu>li").click(function () {
        $("body").hasClass("mini-navbar") && NavToggle()
    });

    $("#side-menu>li li a").click(function () {
        $(window).width() < 769 && NavToggle()
    });

    $(".nav-close").click(NavToggle);

    if(!!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/)){
        $('a[class*="J_menuItem"]').removeClass('J_menuItem').attr('target', '_blank');
    }

});

$(window).on("load resize", function () {
    $(this).width() < 769 && ($("body").addClass("mini-navbar"), $(".navbar-static-side").fadeIn())
});

function NavToggle() {
    $(".navbar-minimalize").trigger("click")
}

function SmoothlyMenu() {
    $("body").hasClass("mini-navbar") ? $("body").hasClass("fixed-sidebar") ? ($("#side-menu").hide(), setTimeout(function () { $("#side-menu").fadeIn(500) }, 300)) : $("#side-menu").removeAttr("style") : ($("#side-menu").hide(), setTimeout(function () { $("#side-menu").fadeIn(500) }, 100))
}

function localStorageSupport() {
    return "localStorage" in window && null !== window.localStorage
}
