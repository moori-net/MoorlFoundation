import template from './index.html.twig';

Shopware.Component.register('moorl-page-detail', {
    template,

    props: {
        entity: {
            type: String,
            required: true,
        },
        componentName: {
            type: String,
            required: true
        },
        item: {
            type: Object,
            required: true,
        }
    }
});
