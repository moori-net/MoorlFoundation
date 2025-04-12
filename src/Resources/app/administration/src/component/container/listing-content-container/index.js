import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-listing-content-container', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
