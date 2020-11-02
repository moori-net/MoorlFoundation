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
        criteria: {
            type: Object,
            required: false,
            default() {
                return new Criteria(1, 10);
            }
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
        gridPagesVisible() {
            return 7;
        },
        gridSteps() {
            return [10, 25, 50];
        },
        gridColumns() {
            let columns = [];
            let properties = Shopware.EntityDefinition.get(this.entity).properties
            let primary = true;

            for (const [property, item] of Object.entries(properties)) {
                item.inlineEdit = null;
                item.fieldType = 'text';

                switch (item.type) {
                    case 'uuid':
                        continue;
                    case "text":
                        item.inlineEdit = 'string';
                        break;
                    case "int":
                        item.inlineEdit = 'int';
                        item.fieldType = 'number';
                        break;
                    case "bool":
                        item.inlineEdit = 'bool';
                        item.fieldType = 'switch';
                        break;
                }

                columns.push({
                    property: property,
                    dataIndex: property,
                    primary: primary,
                    allowResize: false,
                    label: this.$tc(`moorl-foundation.properties.${property}`),
                    inlineEdit: item.inlineEdit,
                    sortable: true,
                    fieldType: item.fieldType
                });

                primary = false;
            }

            console.log(columns);

            return columns;
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
