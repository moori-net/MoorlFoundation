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

import MoorlFoundation from './moorl-foundation/moorl-foundation';
import MoorlFoundationForm from './moorl-foundation-form/moorl-foundation-form';
import MoorlAnimation from './moorl-animation/moorl-animation';

const PluginManager = window.PluginManager;
PluginManager.register('MoorlFoundation', MoorlFoundation);
PluginManager.register('MoorlFoundationForm', MoorlFoundationForm, '[data-moorl-foundation-form]');
PluginManager.register('MoorlAnimation', MoorlAnimation, '[data-moorl-animation]');

if (window.moorlAnimation) {
    for (let item of window.moorlAnimation) {
        PluginManager.register('MoorlAnimation', MoorlAnimation, item.cssSelector, item);
    }
}


if (module.hot) {
    module.hot.accept();
}
