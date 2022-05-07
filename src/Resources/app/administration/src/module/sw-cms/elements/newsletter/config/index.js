const { Component, Mixin } = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-moorl-newsletter', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return{
            snippetPrefix: 'sw-cms.elements.moorl-newsletter.',
        }
    },

    computed: {
    },

    created() {
        this.createdComponent();
    },

    methods: {

    }
});
