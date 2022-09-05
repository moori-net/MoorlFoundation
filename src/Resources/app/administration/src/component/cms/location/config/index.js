const {Component, Mixin} = Shopware;

import template from './index.html.twig';

Component.register('sw-cms-el-config-moorl-location', {
    template,

    props: {
        element: {
            type: Object,
            required: false,
            default: null,
        }
    },

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!Object.keys(this.element.config).length) {
                this.initElementConfig('moorl-location');
            }
        }
    }
});
