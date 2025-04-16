import template from './index.html.twig';

Shopware.Component.register('moorl-custom-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
