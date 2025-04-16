import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-config-moorl-table-registration', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-table-registration');
        },
    },
});
