import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-cms-cta-banner-config', {
    template,

    mixins: [Shopware.Mixin.getByName('moorl-abstract-cms-element')],

    computed: {
        currentType() {
            return this.getValue('elementType');
        },

        currentEntity() {
            return this.element.config[this.currentType]?.entity ?? {};
        }
    },

    created() {
        this.initCmsConfig();
    }
});
