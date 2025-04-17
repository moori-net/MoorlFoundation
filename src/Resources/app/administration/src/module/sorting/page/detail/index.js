import template from './index.html.twig';

Shopware.Component.extend('moorl-sorting-detail', 'moorl-abstract-page-detail', {
    template,

    data() {
        return {
            entity: 'moorl_sorting',
            toBeDeletedCriteria: null,
        };
    },

    computed: {
        entityOptions() {
            const storeOptions = [];
            const definitionRegistry = Shopware.EntityDefinition.getDefinitionRegistry();

            definitionRegistry.forEach(function (value, key) {
                storeOptions.push({
                    name: `${key}`,
                });
            });

            return storeOptions;
        },
    },

    methods: {
        getCriteriaTemplate(fieldName) {
            return {
                field: fieldName,
                order: 'asc',
                priority: 1,
                naturalSorting: 0,
            };
        },

        onDeleteCriteria(toBeRemovedItem) {
            this.toBeDeletedCriteria = toBeRemovedItem;
        },

        onConfirmDeleteCriteria() {
            this.item.fields = this.item.fields.filter((currentCriteria) => {
                return currentCriteria.field !== this.toBeDeletedCriteria.field;
            });
            this.saveProductSorting();
            this.toBeDeletedCriteria = null;
        },

        onAddCriteria(fieldName) {
            if (!fieldName) {
                return;
            }

            const newCriteria = this.getCriteriaTemplate(fieldName);

            if (!this.item.fields) {
                this.item.fields = [];
            }

            this.item.fields.push(newCriteria);
        },

        onCancelEditCriteria(item) {
            if (this.getProductSortingEntityId()) {
                this.fetchProductSortingEntity();

                return;
            }

            this.item.fields = this.item.fields.filter((currentCriteria) => {
                return currentCriteria.field !== item.field;
            });
        }
    },
});
