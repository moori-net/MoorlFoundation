import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-cms-element-config-form', {
    template,

    mixins: [Shopware.Mixin.getByName('moorl-form')],
});
