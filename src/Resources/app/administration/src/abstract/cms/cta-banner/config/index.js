import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-cms-cta-banner-config', {
    template,

    emits: ['element-update'],

    mixins: [Shopware.Mixin.getByName('cms-element')],

    data() {
        return {
            cmsElementMapping: null,
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
            this.cmsElementMapping = this.cmsElements[this.elementType].cmsElementMapping;

            this.isLoading = false;
        }
    },
});
