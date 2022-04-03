/* jQuery extensions */
$.fn.isInViewport = function () {
    if ($(this).length == 0) {
        return;
    }
    let elementTop = $(this).offset().top;
    let elementBottom = elementTop + $(this).outerHeight();
    let viewportTop = $(window).scrollTop();
    let viewportBottom = viewportTop + $(window).height();
    return elementBottom < viewportBottom;
};

$.fn.isOverBottom = function () {
    if ($(this).length == 0) {
        return;
    }
    let elementTop = $(this).offset().top;
    let viewportTop = $(window).scrollTop();
    let viewportBottom = viewportTop + $(window).height();
    return elementTop < viewportBottom;
};

/* Old plugins */
import MoorlFoundation from './moorl-foundation/moorl-foundation';
import MoorlFoundationForm from './moorl-foundation-form/moorl-foundation-form';
import MoorlAnimation from './moorl-animation/moorl-animation';
/* New plugins */
import MoorlRelativeTimePlugin from './relative-time/relative-time.plugin';
import MoorlCountdownPlugin from './countdown/countdown.plugin';
import MoorlProductBuyListPlugin from './product-buy-list/product-buy-list.plugin';
import MoorlTocPlugin from './toc/toc.plugin';

const PluginManager = window.PluginManager;
/* Old plugins */
PluginManager.register('MoorlFoundation', MoorlFoundation);
PluginManager.register('MoorlFoundationForm', MoorlFoundationForm, '[data-moorl-foundation-form]');
PluginManager.register('MoorlAnimation', MoorlAnimation, '[data-moorl-animation]');
/* New plugins */
PluginManager.register('MoorlRelativeTime', MoorlRelativeTimePlugin, '[data-moorl-relative-time]');
PluginManager.register('MoorlCountdown', MoorlCountdownPlugin, '[data-moorl-countdown]');
PluginManager.register('MoorlProductBuyList', MoorlProductBuyListPlugin, '[data-moorl-product-buy-list]');
PluginManager.register('MoorlToc', MoorlTocPlugin, '[data-moorl-toc]');

if (window.moorlAnimation) {
    for (let item of window.moorlAnimation) {
        PluginManager.register('MoorlAnimation', MoorlAnimation, item.cssSelector, item);
    }
}

if (module.hot) {
    module.hot.accept();
}
