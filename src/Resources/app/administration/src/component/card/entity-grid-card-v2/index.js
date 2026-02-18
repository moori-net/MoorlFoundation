import template from './index.html.twig';

Shopware.Component.register('moorl-entity-grid-card-v2', {
    template,

    computed: {
        isReady() {
            if (!this.$attrs.defaultItem || Object.values(this.$attrs.defaultItem).length === 0) {
                return true;
            }

            return Object.values(this.$attrs.defaultItem).every(value => value !== undefined);
        },

        cardTitle() {
            return this.$attrs.title ?? this.$tc(`global.entities.${this.$attrs.entity}`);
        }
    }
});
