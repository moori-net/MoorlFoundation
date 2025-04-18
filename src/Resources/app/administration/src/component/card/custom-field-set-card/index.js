import template from './index.html.twig';

Shopware.Component.register('moorl-custom-field-set-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        },
        sets: {
            type: Array,
            required: false,
            default: [],
        },
    }
});
