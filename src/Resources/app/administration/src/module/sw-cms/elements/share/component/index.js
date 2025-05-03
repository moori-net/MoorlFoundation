import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-share', {
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
            if (Object.keys(this.element.config).length) {
                return;
            }

            this.initElementConfig('moorl-share');
        },
    },
});
