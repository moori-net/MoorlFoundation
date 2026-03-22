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
            showTranslated: false
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

                let label = this.showTranslated ? this.$tc(`global.entities.${key}`) : key;

                if (label === `global.entities.${key}`) {
                    label = key;
                }

                storeOptions.push({
                    label: label,
                    value: `${key}`,
                });
            });

            storeOptions.sort((a, b) => a.label.localeCompare(b.label));

            return storeOptions;
        },
    },
});
