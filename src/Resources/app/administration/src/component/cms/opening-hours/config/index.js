import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-config-moorl-opening-hours', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],
});
