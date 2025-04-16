import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-config-moorl-usp', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-usp');
            this.initElementData('moorl-usp');
        },
    },
});
