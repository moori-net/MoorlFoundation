import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-config-moorl-html-tag', {
    template,

    mixins: [
        Shopware.Mixin.getByName('cms-element')
    ],

    inject: [
        'repositoryFactory'
    ],

    computed: {},

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-html-tag');
        }
    }
});
