const { Component, Mixin } = Shopware;

import template from './index.html.twig';

Component.register('sw-cms-el-config-moorl-usp', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-usp');
            this.initElementData('moorl-usp');
        }
    }
});
