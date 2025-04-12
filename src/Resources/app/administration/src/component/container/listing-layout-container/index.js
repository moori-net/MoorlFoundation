import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-listing-layout-container', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
