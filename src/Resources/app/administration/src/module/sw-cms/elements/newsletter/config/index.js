const { Component, Mixin } = Shopware;

import template from './index.html.twig';

Component.register('sw-cms-el-config-moorl-newsletter', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('cms-element')
    ]
});
