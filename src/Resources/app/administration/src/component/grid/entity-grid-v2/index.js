import template from './index.html.twig';
import './index.scss';

const { Criteria } = Shopware.Data;

/**
 * Standard variable names
 * @items = All data of table: Array
 * @selectedItems: Array
 * @selectedItem: Data row currently editing: Object
 * @entity = Storage name of table
 * @defaultItem = Override of item, Usage: Criteria
 * Shopware Standard
 * @sortBy: String
 * @sortDirection
 * @totalCount: Int
 * @isLoading: bool
 * @page: Int
 * @limit: Int
 * @searchTerm: String
 */
Shopware.Component.register('moorl-entity-grid-v2', {
    inject: ['repositoryFactory', 'acl'],

    template,

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder'),
    ],

    props: {
        componentName: {
            type: String,
            required: false,
            default: 'moorl-foundation',
        },
        entity: {
            type: String,
            required: true,
        },
        defaultItem: {
            type: Object,
            required: false,
            default() {
                return {};
            },
        },
        criteria: {
            type: Object,
            required: false,
            default: undefined,
        },
        topBarOptions: {
            type: Array,
            required: false,
            default: ['search', 'new', 'import', 'export'],
        },
        confirmDelete: {
            type: Boolean,
            required: false,
            default: true,
        },
        /* Handling for prices */
        tax: {
            type: Object,
            required: false,
            default: null,
        },
        defaultCurrency: {
            type: Object,
            required: false,
            default: null,
        },
        priceProperties: {
            type: Array,
            required: false,
            default: ['price'],
        },
    },

    data() {
        return {
            items: null,
            selectedItems: null,
            selectedItem: null,
            totalCount: 0,
            page: 1,
            limit: 10,
            searchTerm: null,
            isLoading: false,
            sortBy: 'createdAt',
            sortDirection: 'ASC',
            showEditModal: false,
            showImportModal: false,
            showExportModal: false,
            deleteId: null,
            currentDefaultCurrency: null,
            currentTax: null,
            listHelper: null,
            ready: false
        };
    },

    computed: {
        defaultCriteria() {
            const criteria = this.criteria || new Criteria();

            this.listHelper.getAssociations().forEach(association => {
                criteria.addAssociation(association);
            });

            for (const [field, value] of Object.entries(this.defaultItem)) {
                criteria.addFilter(Criteria.equals(field, value));
            }

            return criteria;
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
        },
        gridColumns() {
            return this.listHelper.getColumns();
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        async initListHelper() {
            if (!this.listHelper) {
                this.listHelper = new MoorlFoundation.ListHelper({
                    componentName: this.componentName,
                    entity: this.entity,
                    tc: this.$tc
                });

                await this.listHelper.ready;

                this.ready = true;
            }
        },

        async createdComponent() {
            await this.initListHelper();

            this.sortBy = this.listHelper.getSortBy();

            this.loadTax();
            this.loadTax();
            this.loadDefaultCurrency();
            this.getItems();
        },

        loadTax() {
            if (this.tax) {
                this.currentTax = this.tax;
                return;
            }
            return this.taxRepository
                .search(new Criteria(1, 500))
                .then((taxes) => {
                    this.currentTax = taxes[0];
                });
        },

        loadDefaultCurrency() {
            if (this.defaultCurrency) {
                this.currentDefaultCurrency = this.defaultCurrency;
                return;
            }
            this.currencyRepository
                .search(new Criteria(1, 500))
                .then((currencies) => {
                    this.currentDefaultCurrency = currencies.find(
                        (currency) => currency.isSystemDefault
                    );
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

            this.repository
                .search(criteria, Shopware.Context.api)
                .then((items) => {
                    this.totalCount = items.total;
                    this.items = items;
                    this.isLoading = false;

                    if (this.totalCount > 0 && this.items.length <= 0) {
                        this.page = this.page === 1 ? 1 : (this.page -= 1);
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
                    this.deleteId = null;
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

        onSaveItem() {
            this.isLoading = true;

            this.repository
                .save(this.selectedItem, Shopware.Context.api)
                .then(() => {
                    this.getItems();

                    this.showEditModal = false;
                })
                .catch((error) => {
                    this.createNotificationError({ message: error.message });
                });
        },

        onEditItem(item) {
            this.selectedItems = null;

            if (item !== undefined) {
                this.selectedItem = item;
            } else {
                this.selectedItem = this.repository.create(Shopware.Context.api);

                Object.assign(this.selectedItem, this.defaultItem);
            }

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
        },
    },
});
