import template from './index.html.twig';

Shopware.Component.register('moorl-listing-item-fieldset', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
