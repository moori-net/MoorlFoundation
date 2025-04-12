import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-listing-slider-container', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
