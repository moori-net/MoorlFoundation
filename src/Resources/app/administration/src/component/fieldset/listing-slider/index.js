import template from './index.html.twig';

Shopware.Component.register('moorl-listing-slider-fieldset', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
