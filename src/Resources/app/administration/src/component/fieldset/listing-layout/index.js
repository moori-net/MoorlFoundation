import template from './index.html.twig';

Shopware.Component.register('moorl-listing-layout-fieldset', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        },
    },
});
