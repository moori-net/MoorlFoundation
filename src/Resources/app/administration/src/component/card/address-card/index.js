import template from './index.html.twig';

Shopware.Component.register('moorl-address-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        },
    },
});
