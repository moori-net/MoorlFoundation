import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-cms-cta-banner-config', {
    template,

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
        },

        currentType() {
            return this.getValue('elementType');
        },

        currentEntity() {
            return this.element.config[this.currentType]?.entity ?? {};
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.cmsElementMapping = this.cmsElements[this.elementType].cmsElementMapping;

            this.isLoading = false;
        },

        getValue(key) {
            return this.element.config?.[key]?.value ?? null;
        }
    },
});
