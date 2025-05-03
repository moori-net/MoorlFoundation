import template from './index.html.twig';

Shopware.Component.register('moorl-router-link', {
    template,

    props: {
        path: {
            type: String,
            required: true,
        },
        snippet: {
            type: String,
            required: false,
            default: 'moorl-router-link.label',
        },
    },

    computed: {
        routerLink() {
            return this.$tc('moorl-router-link.routerLink') + this.path;
        },
    },
});
