import template from './index.html.twig';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-config-moorl-table-registration', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-table-registration');
        }
    }
});
