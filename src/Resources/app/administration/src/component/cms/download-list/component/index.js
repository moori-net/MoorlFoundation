import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-download-list', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')]
});
