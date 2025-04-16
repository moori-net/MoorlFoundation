import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-config-moorl-address', {
    template,

    props: {
        element: {
            type: Object,
            required: false,
            default: null,
        },
    },

    mixins: [Shopware.Mixin.getByName('cms-element')],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!Object.keys(this.element.config).length) {
                this.initElementConfig('moorl-address');
            }
        },
    },
});
