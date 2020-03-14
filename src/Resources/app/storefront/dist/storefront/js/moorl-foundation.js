$.fn.isInViewport = function () {
    if ($(this).length == 0) {
        return;
    }
    var elementTop = $(this).offset().top;
    var elementBottom = elementTop + $(this).outerHeight();
    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();
    return elementBottom > viewportTop && elementTop < viewportBottom;
};

$.fn.isOverBottom = function () {
    if ($(this).length == 0) {
        return;
    }
    var elementTop = $(this).offset().top;
    var viewportTop = $(window).scrollTop();
    var viewportBottom = viewportTop + $(window).height();
    return elementTop < viewportBottom;
};

const MoorlFoundationAnimateInit = function (str, attr) {
    if (str.trim().length == 0) {
        return;
    }
    let lines = str.split(/;/g);
    if (!lines || typeof lines != 'object') {
        console.log("MoorlFoundation warning: Misconfiguration at animation settings");
        console.log(lines);
        console.log(str);
        console.log(attr);
        return;
    }
    lines.forEach(function (line) {
        let config = line.split(/\|/g);
        if (typeof config[1] == 'string') {
            $(config[0]).attr(attr, config[1]);
        } else {
            console.log("MoorlFoundation warning: Misconfiguration at animation settings");
            console.log(config);
            console.log(str);
            console.log(attr);
        }
    });
};

const MoorlFoundationAnimateIn = function (el) {
    let isVisible = $(el).isOverBottom();
    if (isVisible) {
        $(el).addClass("animated").addClass(el.dataset.animateIn).removeClass("moorl-foundation-hide");
        setTimeout(function () {
            $(el).removeClass(el.dataset.animateIn).removeClass("animated");
        }, 1000);
    }
};

const MoorlFoundationAnimateOut = function (el) {
    let isVisible = $(el).isOverBottom();
    if (!isVisible) {
        $(el).addClass("animated").addClass(el.dataset.animateOut);
        setTimeout(function () {
            $(el).removeClass(el.dataset.animateOut).addClass("moorl-foundation-hide").removeClass("animated");
            MoorlFoundationAnimate();
        }, 1000);
    }
};

const MoorlFoundationMouseOver = function (el) {
    console.log(el);
    $(el).addClass("animated").addClass(el.dataset.animateHover);
    setTimeout(function () {
        $(el).removeClass(el.dataset.animateHover).removeClass("animated");
    }, 1000);
};

const MoorlFoundationAnimate = function () {
    $("[data-animate-in]").each(function () {
        let ready = ($(this).hasClass("moorl-foundation-hide") && !$(this).hasClass("animated"));
        if (ready) {
            MoorlFoundationAnimateIn(this);
        }
    });
    $("[data-animate-out]").each(function () {
        let ready = (!$(this).hasClass("moorl-foundation-hide") && !$(this).hasClass("animated"));
        if (ready) {
            MoorlFoundationAnimateOut(this);
        }
    });
};

$(document).ready(function () {

    MoorlFoundationAnimateInit(MoorlFoundationAnimateConfig.animateIn, 'data-animate-in');
    MoorlFoundationAnimateInit(MoorlFoundationAnimateConfig.animateOut, 'data-animate-out');
    MoorlFoundationAnimateInit(MoorlFoundationAnimateConfig.hover, 'data-animate-hover');

    $("[data-animate-in],[data-animate-out]").each(function () {
        let isVisible = $(this).isOverBottom();
        if (!isVisible) {
            $(this).addClass("moorl-foundation-hide");
        }
    });

    $("[data-animate-hover]").on("mouseover", function () {
        let ready = !$(this).hasClass("animated");
        if (ready) {
            MoorlFoundationMouseOver(this);
        }
    });

    window.onscroll = function () {
        MoorlFoundationAnimate();
    };

});
