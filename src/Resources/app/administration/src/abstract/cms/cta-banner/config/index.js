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

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            const elementType = this.element.type;
            await this.initElementConfig(elementType);
            await this.initElementData(elementType);
            this.cmsElementMapping = this.cmsElements[elementType].cmsElementMapping;
            this.isLoading = false;
        }
    },
});
