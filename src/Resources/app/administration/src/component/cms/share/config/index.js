const {Component, Mixin} = Shopware;

import template from './index.html.twig';

Component.register('sw-cms-el-config-moorl-share', {
    template,

    props: {
        element: {
            type: Object,
            required: false,
            default: null,
        }
    },

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        elementOptions() {
            return {
                provider: [
                    {value: 'facebook', label: 'Facebook'},
                    {value: 'twitter', label: 'Twitter'},
                    {value: 'pinterest', label: 'Pinterest'},
                    {value: 'email', label: 'E-Mail'}
                ]
            };
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!Object.keys(this.element.config).length) {
                this.initElementConfig('moorl-share');
            }
        }
    }
});
