import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-company-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
