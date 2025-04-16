import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-config-moorl-opening-hours', {
    template,

    props: {
        element: {
            type: Object,
            required: false,
            default: null,
        }
    },

    mixins: [
        Shopware.Mixin.getByName('cms-element')
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
                this.initElementConfig('moorl-opening-hours');
            }
        }
    }
});
