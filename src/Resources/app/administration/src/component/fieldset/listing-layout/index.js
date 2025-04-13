import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-listing-layout-fieldset', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
