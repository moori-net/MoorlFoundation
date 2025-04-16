import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-download-list', {
    template,

    props: {
        element: {
            type: Object,
            required: false,
            default: null,
        },
    },

    mixins: [Shopware.Mixin.getByName('cms-element')],

    inject: ['repositoryFactory'],

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            },
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-download-list');
            this.initElementData('moorl-download-list');
        },
    },
});
