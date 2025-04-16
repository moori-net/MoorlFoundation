import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-table-registration', {
    template,

    components: {},

    mixins: [
        Shopware.Mixin.getByName('cms-element')
    ],

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
