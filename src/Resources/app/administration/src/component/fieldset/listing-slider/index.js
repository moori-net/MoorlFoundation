import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-listing-slider-fieldset', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
