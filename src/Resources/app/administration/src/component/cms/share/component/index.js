const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-moorl-share', {
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

    inject: [
        'repositoryFactory'
    ],

    data() {},

    computed: {},

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (Object.keys(this.element.config).length) {
                return;
            }

            this.initElementConfig('moorl-share');
        }
    }
});
