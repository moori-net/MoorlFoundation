import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-entity-definition-select', {
    template,

    props: {
        translatable: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            showTranslated: true
        };
    },

    computed: {
        entityDefinitionOptions() {
            const storeOptions = [];
            const definitionRegistry = Shopware.EntityDefinition.getDefinitionRegistry();

            definitionRegistry.forEach((value, key) => {
                if (this.translatable && value.properties.translations === undefined) {
                    return;
                }

                if (this.showTranslated && this.$tc(`global.entities.${key}`) === `global.entities.${key}`) {
                    return;
                }

                storeOptions.push({
                    label: this.$tc(`global.entities.${key}`),
                    value: `${key}`,
                });
            });

            storeOptions.sort((a, b) => a.label.localeCompare(b.label));

            return storeOptions;
        },
    },
});
