import template from './index.html.twig';

const {Component} = Shopware;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('moorl-thing-seo-card', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        item: {
            type: Object,
            required: true,
        },
        hidden: {
            type: Array,
            required: false,
            default: []
        }
    },

    methods: {
        isVisible(property) {
            return !this.hidden.includes(property);
        }
    }
});
