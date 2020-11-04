import template from './index.html.twig';
import './index.scss';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;


/**
 * Standard vaiable names
 * @items = All data of table: Array
 * @item = One data row of table: Object
 * @selectedItems: Array
 * @selectedItem: Data row currently editing: Object
 * @columns = All columns of table: Array
 * @column = One column of table: Object
 * @mapping = import to export relation: Object
 * @field = Name of data field: String
 * @property = dot separated field names: String
 * @value = Value of data field: Mixed
 * @entity = Storage name of table
 *
 * New
 * @defaultItem = Override of item, Usage: Criteria
 * @filterColumns = Property names of visible columns: Array
 *
 *
 * Shopware Standard
 * @sortBy: String
 * @sortDirection
 * @totalCount: Int
 * @isLoading: bool
 * @page: Int
 * @limit: Int
 * @searchTerm: String
 *
 */
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
                return {};
            }
        },
        depth: {
            type: Number,
            required: false,
            default: 1
        },
        defaultItem: {
            type: Object,
            required: false,
            default() {
                return {};
            }
        }
    },

    data() {
        return {
            items: null,
            item: null,
            selectedItems: null,
            selectedItem: null,
            columns: null,
            gridColumns: null,
            editColumns: null,
            column: null,
            mapping: null,
            field: null,
            value: null,
            entity: null,

            sortBy: 'createdAt',
            sortDirection: 'DESC',
            totalCount: 0,
            page: 1,
            limit: 10,
            searchTerm: null,
            isLoading: false,

            showEditModal: false,
            showImportModal: false,
        };
    },

    computed: {
        defaultCriteria() {
            //const criteria = Object.assign({}, this.criteria);
            const criteria = new Criteria();

            if (this.criteria instanceof Criteria) {
                criteria.associations = this.criteria.associations;
                criteria.aggregations = this.criteria.aggregations;
                criteria.grouping = this.criteria.grouping;
            }

            for (const [field, value] of Object.entries(this.defaultItem)) {
                criteria.addFilter(Criteria.equals(field, value));
            }

            return criteria;
        },
        gridColumns() {
            if (this.columns) {
                return this.columns;
            } else {
                return this.gridColumns;
            }
        },
        gridPagesVisible() {
            return 7;
        },
        gridSteps() {
            return [10, 25, 50, 100, 500];
        },
        repository() {
            return this.repositoryFactory.create(this.entity);
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.initGridColumns();
            console.log("initGridColumns");
            this.initEditColumns();
            console.log("initEditColumns");
            this.getItems();
            console.log("getItems");
        },

        initGridColumns() {
            this.gridColumns = this.getGridColumns();

            console.log("grid col rdy");
            console.log(this.gridColumns);

        },

        initEditColumns() {
            let columns = [];
            let properties = Shopware.EntityDefinition.get(this.entity).properties

            for (const [property, column] of Object.entries(properties)) {
                switch (column.type) {
                    case 'uuid':
                    case 'json_object':
                        continue;
                }

                if (Object.keys(this.defaultItem).indexOf(property) !== -1) {
                    continue;
                }

                if (!column.flags.moorl_edit_field) {
                    continue;
                }

                column.property = property;
                column.label = this.$tc(`moorl-foundation.properties.${property}`);

                columns.push(column);
            }

            this.editColumns = columns;
        },

        getGridColumns(entityName, prefix, depth) {
            let primary = false;

            if (!entityName) {
                entityName = this.entity;
                primary = true;
                prefix = '';
                depth = 0;
            } else {
                prefix = prefix + '.';
                depth++;
                if (depth > this.depth) {
                    return [];
                }
            }

            let columns = [];
            let properties = Shopware.EntityDefinition.get(entityName).properties

            for (const [property, column] of Object.entries(properties)) {
                let propertyName = prefix + property;

                column.inlineEdit = false;
                column.fieldType = column.type;

                switch (column.type) {
                    case 'uuid':
                    case 'json_object':
                        continue;
                    case 'association':
                        columns = [...columns, ...this.getGridColumns(column.entity, propertyName, depth)];
                        continue;
                    case "text":
                        column.inlineEdit = 'string';
                        break;
                    case "int":
                        column.inlineEdit = 'int';
                        column.fieldType = 'number';
                        break;
                    case "boolean":
                        column.inlineEdit = 'bool';
                        column.fieldType = 'switch';
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
                    inlineEdit: column.inlineEdit,
                    sortable: true,
                    fieldType: column.fieldType
                });

                primary = false;
            }

            return columns;
        },

        onPageChange(data) {
            this.page = data.page;
            this.limit = data.limit;

            this.getItems();
        },

        getItems() {
            this.isLoading = true;
            const criteria = this.defaultCriteria;

            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, true))
            criteria.setPage(this.page);
            criteria.setLimit(this.limit);
            criteria.setTotalCountMode(1);
            if (this.searchTerm) {
                criteria.setTerm(this.searchTerm);
            }

            this.repository.search(criteria, Shopware.Context.api).then((items) => {
                this.totalCount = items.total;
                this.items = items;
                this.isLoading = false;

                if (this.totalCount > 0 && this.items.length <= 0) {
                    this.page = (this.page === 1) ? 1 : this.page -= 1;
                    this.getItems();
                }
            });
        },

        onSelectionChanged(selection) {
            this.selectedItems = selection;
        },

        onSortColumn(column) {
            if (column.dataIndex !== this.sortBy) {
                this.sortBy = column.dataIndex;
                this.sortDirection = 'ASC';
                this.getItems();
                return;
            }

            if (this.sortDirection === 'ASC') {
                this.sortDirection = 'DESC';
            } else {
                this.sortDirection = 'ASC';
            }

            this.getItems();
        },

        onSearch() {
            this.page = 1;
            this.getItems();
        },

        onDeleteItem(item) {
            this.repository
                .delete(item.id, Shopware.Context.api)
                .then(() => {
                    this.getItems();
                });
        },

        onDeleteSelectedItems() {
            this.isLoading = true;
            const promises = [];

            Object.keys(this.selectedItems).forEach((id) => {
                promises.push(this.repository.delete(id, Shopware.Context.api));
            });

            this.selectedItems = {};

            Promise.all(promises).then(() => {
                this.getItems();
            });
        },

        onUpdateSelectedItems() {
            const promises = [];

            Object.values(this.selectedItems).forEach((item) => {
                Object.assign(item, this.selectedItem);

                promises.push(this.repository.save(item, Shopware.Context.api));
            });

            this.selectedItems = {};
            this.selectedItem = {};

            Promise.all(promises).then(() => {
                this.getItems();
                this.showEditModal = false;
            });
        },

        onSaveItem() {
            this.isLoading = true;

            if (!this.selectedItem.id) {
                this.onUpdateSelectedItems();
                return;
            }

            this.repository
                .save(this.selectedItem, Shopware.Context.api)
                .then(() => {
                    this.getItems();
                    this.showEditModal = false;
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$t('moorl-foundation.notification.saveError'),
                        message: exception
                    });
                });
        },

        onEditItem(item) {
            this.selectedItems = null;

            if (item) {
                this.selectedItem = item;
                this.showEditModal = true;
            } else {
                this.selectedItem = this.repository.create(Shopware.Context.api);
                Object.assign(this.selectedItem, this.defaultItem);
                this.showEditModal = true;
            }
        },

        onEditSelectedItems() {
            this.selectedItem = Object.assign({}, this.defaultItem);
            this.showEditModal = true;
        },

        onImportModal() {
            this.showImportModal = true;
        },

        onCloseModal() {
            this.showEditModal = false;
            this.showImportModal = false;
        }
    }
});
