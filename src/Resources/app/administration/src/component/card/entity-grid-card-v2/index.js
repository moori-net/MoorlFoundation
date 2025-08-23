import template from './index.html.twig';

Shopware.Component.register('moorl-entity-grid-card-v2', {
    template,

    computed: {
        cardTitle() {
            return this.$attrs.title ?? this.$tc(`global.entities.${this.$attrs.entity}`);
        }
    }
});
