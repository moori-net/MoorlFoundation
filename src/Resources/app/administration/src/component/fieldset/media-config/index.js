import template from './index.html.twig';

Shopware.Component.register('moorl-media-config', {
    template,

    emits: ['update:value'],

    props: {
        value: {
            type: Object,
            required: true,
            default: {},
        }
    },

    created: function () {
        this.createdComponent();
    },

    methods: {
        createdComponent() {},
    },
});
