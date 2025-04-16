import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-accordion', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-accordion');
            this.initElementData('moorl-accordion');
        },
    },
});
