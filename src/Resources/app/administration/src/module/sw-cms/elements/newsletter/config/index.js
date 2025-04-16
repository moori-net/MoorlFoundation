import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-config-moorl-newsletter', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Shopware.Mixin.getByName('cms-element')
    ]
});
