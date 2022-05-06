import template from './index.html.twig';
import './index.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-appflix-table-registration', {
    template,

    components: {},

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.appflix-table-registration.'
        };
    },

    computed: {},

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('appflix-table-registration');
        }
    }
});
