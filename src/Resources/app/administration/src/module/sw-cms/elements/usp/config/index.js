const { Component, Mixin } = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-moorl-usp', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.moorl-usp.',
        };
    },


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
