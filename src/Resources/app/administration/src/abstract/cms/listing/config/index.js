import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-listing-config', {
    template,

    mixins: [Shopware.Mixin.getByName('moorl-abstract-cms-element')],

    created() {
        this.initCmsConfig();
    }
});
