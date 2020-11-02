import template from './index.html.twig';
import './index.scss';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;

Component.register('moorl-entity-grid', {
    inject: ['repositoryFactory', 'acl'],

    template,

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    props: {
        entity: {
            type: String,
            required: true
        },
        columns: {
            type: Array,
            required: false
        },
        filterColumns: {
            type: Array,
            required: false,
            default: []
        },
        criteria: {
            type: Object,
            required: false,
            default() {
                return new Criteria(1, 10);
            }
        },
        depth: {
            type: Number,
            required: false,
            default: 1
        },
        onEditItem: {
            type: Function,
            required: false,
            default: null
        },
        onImportItems : {
            type: Function,
            required: false,
            default: null
        }
    },

    data() {
        return {
            totalCount: 0,
            gridCurrentPageNr: 1,
            gridPageLimit: 10,
            gridPageDataSource: [],
            gridSearch: null
        };
    },

    computed: {
        gridColumns() {
            if (this.columns) {
                return this.columns;
            }

            return this.initGridColumns(null);
        },
        gridPagesVisible() {
            return 7;
        },
        gridSteps() {
            return [10, 25, 50];
        },
        gridItemsTotal() {
            return this.totalCount;
        },
        repository() {
            return this.repositoryFactory.create(this.entity);
        },
        defaultCriteria() {
            return this.criteria;
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.refreshGridDataSource();
        },

        initGridColumns(entityName, prefix, depth) {
            let primary = false;

            if (!entityName) {
                entityName = this.entity;
                primary = true;
                prefix = '';
                depth = 0;
            } else {
                console.log(this.depth);
                prefix = prefix + '.';
                depth++;
                if (depth > this.depth) {
                    return [];
                }
            }

            let columns = [];
            let properties = Shopware.EntityDefinition.get(entityName).properties

            for (const [property, item] of Object.entries(properties)) {
                let propertyName = prefix + property;

                item.inlineEdit = false;
                item.fieldType = item.type;

                switch (item.type) {
                    case 'uuid':
                    case 'json_object':
                        continue;
                    case 'association':
                        columns = [...columns, ...this.initGridColumns(item.entity, propertyName, depth)];
                        continue;
                    case "text":
                        item.inlineEdit = 'string';
                        break;
                    case "int":
                        item.inlineEdit = 'int';
                        item.fieldType = 'number';
                        break;
                    case "boolean":
                        item.inlineEdit = 'bool';
                        item.fieldType = 'switch';
                        break;
                }

                if (this.filterColumns.length !== 0) {
                    if (this.filterColumns.indexOf(propertyName) === -1) {
                        continue;
                    }
                }

                columns.push({
                    property: propertyName,
                    dataIndex: propertyName,
                    primary: primary,
                    allowResize: false,
                    label: this.$tc(`moorl-foundation.properties.${property}`),
                    inlineEdit: item.inlineEdit,
                    sortable: true,
                    fieldType: item.fieldType
                });

                primary = false;
            }

            return columns;
        },

        onPageChange(data) {
            this.gridCurrentPageNr = data.page;
            this.gridPageLimit = data.limit;

            this.refreshGridDataSource();
        },

        refreshGridDataSource() {
            const criteria = this.defaultCriteria;

            criteria.setPage(this.gridCurrentPageNr);
            criteria.setLimit(this.gridPageLimit);
            criteria.setTotalCountMode(1);
            if (this.gridSearch) {
                criteria.setTerm(this.gridSearch);
            }

            this.repository.search(criteria, Shopware.Context.api).then((items) => {
                this.totalCount = items.total;
                this.gridPageDataSource = items;

                if (this.totalCount > 0 && this.gridPageDataSource.length <= 0) {
                    this.gridCurrentPageNr = (this.gridCurrentPageNr === 1) ? 1 : this.gridCurrentPageNr -= 1;
                    this.refreshGridDataSource();
                }
            });
        },

        onGridSelectionChanged(selection, selectionCount) {
            this.deleteButtonDisabled = selectionCount <= 0;
        },

        onSearch() {
            this.gridCurrentPageNr = 1;
            console.log(this.gridSearch);
            this.refreshGridDataSource();
        },

        onDeleteItem(item) {
            this.repository
                .delete(item.id, Shopware.Context.api)
                .then(() => {
                    this.refreshGridDataSource();
                });
        },

        onEditItem(item) {
            this.onEditItem(item)
        }
    }
});
