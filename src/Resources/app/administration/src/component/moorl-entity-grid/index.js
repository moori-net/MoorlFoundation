import template from './index.html.twig';
import './index.scss';

const { Component, Mixin } = Shopware;
const Criteria = Shopware.Data.Criteria;

/**
 * Standard variable names
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
        path: {
            type: String,
            required: false
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
        snippetSrc: {
            type: String,
            required: false,
            default: 'moorl-foundation'
        },
        excludeInput: {
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
        sortBy: {
            type: String,
            required: false,
            default: 'createdAt'
        },
        sortDirection: {
            type: String,
            required: false,
            default: 'DESC'
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
        },
        topBarOptions: {
            type: Array,
            required: false,
            default: ['search', 'new', 'import', 'export']
        },
        confirmDelete: {
            type: Boolean,
            required: false,
            default: true
        },
        /* Handling for prices */
        tax: {
            type: Object,
            required: false,
            default: null
        },
        defaultCurrency: {
            type: Object,
            required: false,
            default: null
        },
        priceProperties: {
            type: Array,
            required: false,
            default: ['price']
        },
    },

    data() {
        return {
            items: null,
            item: null,
            selectedItems: null,
            selectedItem: null,
            gridColumns: null,
            editColumns: null,
            column: null,
            mapping: null,
            field: null,
            value: null,

            totalCount: 0,
            page: 1,
            limit: 10,
            searchTerm: null,
            isLoading: false,
            sortBy: 'createdAt',
            sortDirection: 'DESC',

            showEditModal: false,
            showImportModal: false,
            showExportModal: false,
            deleteId: null,

            currentDefaultCurrency: null,
            currentTax: null,
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
        },
        taxRepository() {
            return this.repositoryFactory.create('tax');
        },
        currencyRepository() {
            return this.repositoryFactory.create('currency');
        },
        topBarColumns() {
            if (this.topBarOptions.length === 4) {
                return '4fr 1fr 1fr 1fr';
            }
            if (this.topBarOptions.length === 3) {
                return '1fr 1fr 1fr';
            }
            if (this.topBarOptions.length === 2) {
                return '1fr 1fr';
            }
            if (this.topBarOptions.length === 1) {
                return '1fr';
            }
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.loadTax();
            this.loadDefaultCurrency();
            this.initGridColumns();
            this.initEditColumns();
            this.getItems();
        },

        loadTax() {
            if (this.tax) {
                this.currentTax = this.tax;
                return;
            }
            return this.taxRepository.search(new Criteria(1, 500)).then((taxes) => {
                this.currentTax = taxes[0];
            });
        },

        loadDefaultCurrency() {
            if (this.defaultCurrency) {
                this.currentDefaultCurrency = this.defaultCurrency;
                return;
            }
            this.currencyRepository.search(new Criteria(1, 500)).then((currencies) => {
                this.currentDefaultCurrency = currencies.find(currency => currency.isSystemDefault);
            });
        },

        getItemPrice(item) {
            if (item.customPrice) {
                return item.price ? item.price : [];
            } else if (item.accessory) {
                return item.accessory ? item.accessory.price : [];
            } else if (item.product) {
                return item.product ? item.product.price : [];
            } else {
                return item.price ? item.price : [];
            }
        },

        getItemTax(item) {
            if (item.tax) {
                return item.tax;
            } else {
                return this.currentTax;
            }
        },

        initGridColumns() {
            this.gridColumns = this.getGridColumns();
        },

        initEditColumns() {
            let columns = [];
            let properties = Shopware.EntityDefinition.get(this.entity).properties

            for (const [property, column] of Object.entries(properties)) {
                if (column.type === 'uuid') {
                    continue;
                }

                if (column.type === 'json_object' && property !== 'price' && property !== 'svgShape') {
                    //continue;
                }

                if (Object.keys(this.defaultItem).indexOf(property) !== -1) {
                    continue;
                }
                if (this.excludeInput.indexOf(property) !== -1) {
                    continue;
                }

                if (!column.flags.moorl_edit_field && !column.flags.moorl_vue_component) {
                    continue;
                }

                column.property = property;

                column.required = false;
                if (column.type === 'association') {
                    if (properties[column.localField].flags.required) {
                        column.required = true;
                    }
                } else {
                    if (column.flags.required) {
                        column.required = true;
                    }
                }

                column.label = this.$tc(`${this.snippetSrc}.properties.${property}`);

                if (column.flags.moorl_edit_field_options) {
                    if (column.flags.moorl_edit_field_options.tooltip) {
                        column.helpText = this.$tc(column.flags.moorl_edit_field_options.tooltip);
                    }
                    if (column.flags.moorl_edit_field_options.label) {
                        column.label = this.$tc(column.flags.moorl_edit_field_options.label);
                    }
                }

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
                        continue;
                    case 'json_object':
                        break;
                    case 'association':
                        columns = [...columns, ...this.getGridColumns(column.entity, propertyName, depth)];
                        continue;
                    case "text":
                        column.inlineEdit = 'string';
                        break;
                    case "int":
                        column.inlineEdit = 'int';
                        column.fieldType = 'number';
                        column.align = 'right';
                        break;
                    case "boolean":
                        /* sw do not support nested properties boolean */
                        if (depth === 0) {
                            column.inlineEdit = 'boolean';
                            column.fieldType = 'switch';
                        }
                        column.align = 'center';
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
                    label: this.getPropertyLabel(propertyName),
                    inlineEdit: column.inlineEdit,
                    sortable: true,
                    fieldType: column.fieldType,
                    align: column.align ? column.align : 'left'
                });

                primary = false;
            }

            if (depth === 0) {
                //console.log(columns);
            }

            return columns;
        },

        getPropertyLabel(propertyName) {
            let labelParts = [];

            for (let part of propertyName.split(".")) {
                labelParts.push(this.$tc(`${this.snippetSrc}.properties.${part}`))
            }

            return labelParts.join(' - ');
        },

        onPageChange(data) {
            this.page = data.page;
            this.limit = data.limit;

            this.getItems();
        },

        getItems() {
            this.isLoading = true;
            const criteria = this.defaultCriteria;

            criteria.resetSorting();
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
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
            this.deleteId = item.id;
            if (this.confirmDelete) {
                return;
            }
            this.onDeleteItemId();
        },

        onDeleteItemId() {
            this.repository
                .delete(this.deleteId, Shopware.Context.api)
                .then(() => {
                    this.deleteId = null
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

                    const errorCode = Shopware.Utils.get(exception, 'response.data.errors[0].code');

                    if (errorCode === 'MOORL__DUPLICATE_ENTRY') {
                        const parameters = Shopware.Utils.get(exception, 'response.data.errors[0].meta.parameters');
                        const titleSaveError = this.$tc('moorl-foundation.notification.errorTitle');
                        const messageSaveError = this.$tc('moorl-foundation.notification.errorDuplicateEntryText', 0, parameters);
                        this.createNotificationError({
                            title: titleSaveError,
                            message: messageSaveError
                        });
                        return;
                    }

                    const titleSaveError = this.$tc('moorl-foundation.notification.errorTitle');
                    const messageSaveError = this.$tc('moorl-foundation.notification.errorRequiredText');
                    this.createNotificationError({
                        title: titleSaveError,
                        message: messageSaveError
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

                if (Shopware.Context.api.languageId !== Shopware.Context.api.systemLanguageId) {
                    Shopware.State.commit('context/setApiLanguageId', Shopware.Context.api.systemLanguageId)
                }

                for (let column of this.editColumns) {
                    if (column.relation === 'many_to_many' || column.relation === 'one_to_many') {
                        let repository = this.repositoryFactory.create(column.entity);

                        this.selectedItem[column.property] = new Shopware.Data.EntityCollection(
                            repository.route,
                            repository.entityName,
                            Shopware.Context.api
                        );
                    } else if (column.relation === 'many_to_one' && column.localField) {
                        this.selectedItem[column.localField] = null;
                    }
                }

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

        onExportModal() {
            this.showExportModal = true;
        },

        onCloseModal() {
            this.showEditModal = false;
            this.showExportModal = false;
            this.showImportModal = false;
        }
    }
});
