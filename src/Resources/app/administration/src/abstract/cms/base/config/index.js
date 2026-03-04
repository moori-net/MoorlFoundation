import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-cms-base-config', {
    template,

    mixins: [Shopware.Mixin.getByName('moorl-abstract-cms-element')],

    created() {
        this.initCmsConfig();
    }
});
