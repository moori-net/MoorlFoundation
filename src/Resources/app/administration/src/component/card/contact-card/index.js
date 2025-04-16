import template from './index.html.twig';

Shopware.Component.register('moorl-contact-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        }
    }
});
