import template from './index.html.twig';

Shopware.Component.register('moorl-listing-content-fieldset', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        },
    },
});
