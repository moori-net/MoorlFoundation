import template from './index.html.twig';
import './index.scss';

const { Criteria } = Shopware.Data;

Shopware.Component.register('moorl-sorting-option-criteria-card', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('sw-inline-snippet'),
    ],

    props: {
        item: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            customFieldSets: [],
            selectedCriteria: null,
        };
    },

    computed: {
        customFieldRepository() {
            return this.repositoryFactory.create('custom_field');
        },

        customFieldSetRepository() {
            return this.repositoryFactory.create('custom_field_set');
        },

        customFieldSetRelationsRepository() {
            return this.repositoryFactory.create('custom_field_set_relation');
        },

        sortedProductSortingFields() {
            return [...(this.item.fields || [])].sort((a, b) => {
                if (a.priority === b.priority) {
                    return 0;
                }

                return a.priority < b.priority ? 1 : -1;
            });
        },

        itemColumns() {
            return [
                {
                    property: 'field',
                    label: this.$tc('moorl-foundation.field.name'),
                    inlineEdit: 'string',
                },
                {
                    property: 'order',
                    label: this.$tc('moorl-foundation.field.order'),
                    inlineEdit: 'string',
                },
                {
                    property: 'priority',
                    label: this.$tc('moorl-foundation.field.priority'),
                    inlineEdit: 'number',
                },
            ];
        },

        criteriaOptions() {
            const storeOptions = [];
            const entity = this.item?.entity;

            if (!entity) {
                return storeOptions;
            }

            const entityDefinition = Shopware.EntityDefinition.get(entity);

            if (!entityDefinition || !entityDefinition.properties) {
                return storeOptions;
            }

            Object.entries(entityDefinition.properties).forEach(([property, value]) => {
                if (
                    [
                        'uuid',
                        'text',
                        'string',
                        'json_object',
                        'date',
                        'boolean',
                        'int',
                    ].includes(value.type)
                ) {
                    if (property === 'customFields') {
                        this.customFieldSets.forEach((customFieldSet) => {
                            customFieldSet.customFields.forEach((customField) => {
                                storeOptions.push({
                                    value: `${entity}.${property}.${customField.name}`,
                                    label: `${property}.${customField.name}`,
                                    type: `${customField.type}`,
                                });
                            });
                        });
                    } else {
                        storeOptions.push({
                            value: `${entity}.${property}`,
                            label: `${property}`,
                            type: `${value.type}`,
                        });
                    }
                }
            });

            return storeOptions.sort((a, b) => a.label.localeCompare(b.label));
        },

        orderOptions() {
            return [
                {
                    label: this.$tc('global.default.ascending'),
                    value: 'asc',
                },
                {
                    label: this.$tc('global.default.descending'),
                    value: 'desc',
                },
            ];
        },

        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
    },

    watch: {
        'item.entity'(newValue, oldValue) {
            if (!newValue || newValue === oldValue) {
                return;
            }

            this.item.fields = [];

            this.createNotificationInfo({
                message: this.$tc('moorl-sorting-option-criteria-card.entityChangedFieldsReset'),
            });

            this.customFieldSets = [];
            this.selectedCriteria = null;
            this.loadCustomFieldSets();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!this.item.fields) {
                this.item.fields = [];
            }

            this.loadCustomFieldSets();
        },

        loadCustomFieldSets() {
            if (!this.item?.entity) {
                this.customFieldSets = [];
                return;
            }

            const criteria = new Criteria(1, 100);

            criteria.addFilter(
                Criteria.equals('relations.entityName', this.item.entity)
            );

            criteria
                .addAssociation('customFields')
                .addSorting(
                    Criteria.sort('config.customFieldPosition', 'ASC', true)
                );

            this.customFieldSetRepository
                .search(criteria, Shopware.Context.api)
                .then((searchResult) => {
                    this.customFieldSets = searchResult;
                });
        },

        onAddCriteria(fieldName) {
            if (!fieldName) {
                return;
            }

            if (!this.criteriaIsAlreadyUsed(fieldName)) {
                const newCriteria = this.getCriteriaTemplate(fieldName);

                if (!this.item.fields) {
                    this.item.fields = [];
                }

                this.item.fields.push(newCriteria);

                const record = this.item.fields.find((field) => {
                    return field.field === fieldName;
                });

                this.$nextTick().then(() => {
                    if (record && this.$refs.dataGrid) {
                        this.$refs.dataGrid.onDbClickCell(record);
                    }
                });

                return;
            }

            this.createNotificationError({
                message: this.$t('moorl-sorting-option-criteria-card.criteriaAlreadyUsed', {
                    fieldName,
                }),
            });
        },

        getOrderSnippet(order) {
            if (order === 'asc') {
                return this.$tc('global.default.ascending');
            }

            return this.$tc('global.default.descending');
        },

        onRemoveCriteria(item) {
            this.item.fields = (this.item.fields || []).filter((currentCriteria) => {
                return currentCriteria.field !== item.field;
            });
        },

        getCriteriaTemplate(fieldName) {
            return {
                field: fieldName,
                order: 'asc',
                priority: 1,
                naturalSorting: 0,
            };
        },

        onSaveInlineEdit(item) {
            if (item.field === null) {
                this.createNotificationError({
                    message: this.$tc('sorting.general.productSortingCriteriaGrid.options.customFieldCriteriaNotNull'),
                });
            }
        },

        onCancelInlineEdit(item) {
            this.item.fields = (this.item.fields || []).filter((currentCriteria) => {
                return currentCriteria.field !== item.field;
            });
        },

        criteriaIsAlreadyUsed(criteriaName) {
            return (this.item.fields || []).some((currentCriteria) => {
                return currentCriteria.field === criteriaName;
            });
        },
    },
});
