const {Component, Mixin} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-moorl-opening-hours', {
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
            if (Object.keys(this.element.config).length) {
                return;
            }

            this.initElementConfig('moorl-opening-hours');
        }
    }
});
