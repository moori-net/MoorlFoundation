import template from './index.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-appflix-table-registration', {
    template,

    inject: [],

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.appflix-table-registration.'
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {},

    methods: {
        createdComponent() {
            this.initElementConfig('appflix-table-registration');
        }
    }
});
