import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-contact-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
