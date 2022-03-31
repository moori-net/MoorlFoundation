const {Component, Mixin} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-moorl-download-list', {
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

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-download-list');
            this.initElementData('moorl-download-list');
        }
    }
});
