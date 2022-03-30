const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

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

    data() {
        return {
            items: [],
            criteria: new Criteria(1, 12)
        };
    },

    computed: {
        itemClass() {
            let className = "enable-add-to-cart";
            return className;
        }
    },

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
            if (Object.keys(this.element.config).length) {
                return;
            }

            this.initElementConfig('moorl-download-list');
            this.initElementData('moorl-download-list');
        }
    }
});
