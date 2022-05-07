import template from './index.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-moorl-table-registration', {
    template,

    inject: [],

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.moorl-table-registration.'
        };
    },

    created() {
        this.createdComponent();
    },

    computed: {},

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-table-registration');
        }
    }
});
