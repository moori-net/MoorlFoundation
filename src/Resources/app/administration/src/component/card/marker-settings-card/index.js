import template from './index.html.twig';

Shopware.Component.register('moorl-marker-settings-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true
        },
    },

    created() {
        this.item.markerSettings ??= {};
    }
});
