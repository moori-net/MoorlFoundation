import template from './index.html.twig';

Shopware.Component.register('moorl-association-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        },
        entity: {
            type: String,
            required: true,
        }
    },
});
