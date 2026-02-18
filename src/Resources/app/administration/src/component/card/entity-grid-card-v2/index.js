import template from './index.html.twig';

Shopware.Component.register('moorl-entity-grid-card-v2', {
    template,

    props: {
        defaultItem: {
            type: Object,
            required: false,
            default: undefined,
        }
    },

    computed: {
        isReady() {
            if (!this.defaultItem || Object.values(this.defaultItem).length === 0) {
                return true;
            }

            return Object.values(this.defaultItem).every(value => value !== undefined);
        },

        cardTitle() {
            return this.$attrs.title ?? this.$tc(`global.entities.${this.$attrs.entity}`);
        }
    }
});
