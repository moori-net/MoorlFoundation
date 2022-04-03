const {Component, Mixin} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-moorl-toc', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
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
            this.initElementConfig('moorl-toc');
        }
    }
});
