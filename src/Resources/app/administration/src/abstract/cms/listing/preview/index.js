import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-listing-preview', {
    template,
    data() {
        return {
            label: 'sw-cms.elements.moorl-foundation-listing.name',
        };
    },
});
