import template from './index.html.twig';

Shopware.Component.register('moorl-support-link', {
    template,

    props: {
        path: {
            type: String,
            required: true,
        },
        snippet: {
            type: String,
            required: false,
            default: 'moorl-support-link.label',
        },
    },

    computed: {
        supportLink() {
            return this.$tc('moorl-support-link.supportLink') + this.path;
        },
    },
});
