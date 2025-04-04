import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-thing-seo-card', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
