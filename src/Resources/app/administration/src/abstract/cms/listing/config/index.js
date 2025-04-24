import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-listing-config', {
    template,

    emits: ['element-update'],

    mixins: [Shopware.Mixin.getByName('cms-element')],

    data() {
        return {
            cmsElementMapping: null,
            cmsElementEntity: null,
            isLoading: true,
        };
    },

    computed: {
        elementType() {
            return this.element.type;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        elementUpdate() {
            this.$emit('element-update', this.element);
        },

        createdComponent() {
            this.cmsElementEntity = this.cmsElements[this.elementType].cmsElementEntity;
            this.cmsElementMapping = this.cmsElements[this.elementType].cmsElementMapping;

            this.isLoading = false;
        }
    },
});
