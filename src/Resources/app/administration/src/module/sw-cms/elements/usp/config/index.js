const { Component, Mixin } = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-appflix-usp', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.appflix-usp.',
        };
    },


    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('appflix-usp');
            this.initElementData('appflix-usp');
        }
    }
});
