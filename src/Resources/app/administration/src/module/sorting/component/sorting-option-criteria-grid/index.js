import template from './index.html.twig';
import './index.scss';

const {Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Shopware.Component.register('moorl-sorting-option-criteria-grid', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('sw-inline-snippet'),
    ],

    props: {
        item: {
            type: Object,
            required: true,
        }
    },

    data() {
        return {
            customFieldSets: [],
            selectedCriteria: null
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
            return this.item.fields.sort((a, b) => {
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
                    label: this.$tc('moorl-sorting.properties.name'),
                    inlineEdit: 'string',
                },
                {
                    property: 'order',
                    label: this.$tc('moorl-sorting.properties.order'),
                    inlineEdit: 'string',
                },
                {
                    property: 'priority',
                    label: this.$tc('moorl-sorting.properties.priority'),
                    inlineEdit: 'number',
                },
            ];
        },

        criteriaOptions() {
            const storeOptions = [];
            const entity = this.item.entity;
            const entityDefinition = Shopware.EntityDefinition.get(entity).properties;

            Object.entries(entityDefinition).forEach(([property, value]) => {
                if (['uuid', 'text', 'string', 'json_object', 'date', 'boolean', 'int'].indexOf(value.type) !== -1) {
                    if (property === 'customFields') {
                        this.customFieldSets.forEach(function (customFieldSet) {
                            customFieldSet.customFields.forEach(function (customField) {
                                storeOptions.push({
                                    value: `${entity}.${property}.${customField.name}`,
                                    label: `${property}.${customField.name}`,
                                    type: `${customField.type}`
                                });
                            });
                        });
                    } else {
                        storeOptions.push({
                            value: `${entity}.${property}`,
                            label: `${property}`,
                            type: `${value.type}`
                        });
                    }
                }
            });

            return storeOptions.sort((a, b) => {
                return a.label.localeCompare(b.label);
            });
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
    },

    watch: {
        item: {
            handler() {
                if (!this.item || !this.item.fields) {
                    return;
                }

                this.item.fields.forEach(field => {
                    if (field.field === null) {
                        field.field = 'customField';
                    }
                });
            },
            deep: true,
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.loadCustomFieldSets();
        },

        loadCustomFieldSets() {
            const criteria = new Criteria(1, 100);

            criteria.addFilter(Criteria.equals('relations.entityName', this.item.entity));
            criteria.addAssociation('customFields').addSorting(Criteria.sort('config.customFieldPosition', 'ASC', true));

            this.customFieldRepository
                .search(criteria, Shopware.Context.api)
                .then((searchResult) => {
                    this.customFieldSets = searchResult;
                });
        },

        onAddCriteria(fieldName) {
            if (!this.criteriaIsAlreadyUsed(fieldName)) {
                this.$emit('criteria-add', fieldName);

                const record = this.item.fields.find(field => field.field === fieldName);
                this.$nextTick().then(() => {
                    if (record && this.$refs.dataGrid) {
                        this.$refs.dataGrid.onDbClickCell(record);
                    }
                });

                return;
            }

            this.createNotificationError({
                message: this.$t(
                    'sorting.general.productSortingCriteriaGrid.options.criteriaAlreadyUsed',
                    {fieldName},
                ),
            });
        },

        getOrderSnippet(order) {
            if (order === 'asc') {
                return this.$tc('global.default.ascending');
            }

            return this.$tc('global.default.descending');
        },

        onRemoveCriteria(item) {
            this.$emit('criteria-delete', item);
        },

        getCriteriaTemplate(fieldName) {
            return {field: fieldName, order: 'asc', priority: 1, naturalSorting: 0};
        },

        onSaveInlineEdit(item) {
            if (item.field === null) {
                this.createNotificationError({
                    message: this.$t(
                        'sorting.general.productSortingCriteriaGrid.options.customFieldCriteriaNotNull',
                    ),
                });

                return;
            }

            this.$emit('inline-edit-save');
        },

        onCancelInlineEdit(item) {
            this.$emit('inline-edit-cancel', item);
        },

        criteriaIsAlreadyUsed(criteriaName) {
            return this.item.fields.some(currentCriteria => {
                return currentCriteria.field === criteriaName;
            });
        }
    },
});
