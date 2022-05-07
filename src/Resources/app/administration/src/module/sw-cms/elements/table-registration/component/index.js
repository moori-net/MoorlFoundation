import template from './index.html.twig';
import './index.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-moorl-table-registration', {
    template,

    components: {},

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.moorl-table-registration.'
        };
    },

    computed: {},

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-table-registration');
        }
    }
});
