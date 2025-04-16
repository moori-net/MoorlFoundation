import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-preview-moorl-foundation-listing', {
    template,
    data() {
        return {
            label: 'sw-cms.elements.moorl-foundation-listing.name',
        };
    },
});
