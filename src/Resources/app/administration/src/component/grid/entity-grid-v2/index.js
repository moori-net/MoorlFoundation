import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-entity-grid-v2', {
    inject: ['acl'],

    template,

    mixins: [Shopware.Mixin.getByName('moorl-listing')],

    props: {
        entity: {
            type: String,
            required: true,
        },
        componentName: {
            type: String,
            required: false,
            default: 'moorl-foundation',
        },
        defaultItem: {
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
    },

    data() {
        return {
            selectedItems: null,
            selectedItem: null,
            page: 1,
            limit: 10,
            term: null,
            isLoading: true,
            sortBy: 'createdAt',
            sortDirection: 'ASC',
            showEditModal: false,
            showImportModal: false,
            showExportModal: false,
            deleteId: null
        };
    },

    computed: {
        gridPagesVisible() {
            return 7;
        },

        gridSteps() {
            return [10, 25, 50, 100, 500];
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
        async createdComponent() {
            await this.initListHelper();

            this.sortBy = this.listHelper.sortBy;
            this.sortDirection = this.listHelper.sortDirection;

            this.getItems();
        },

        async getItems() {
            await this.loadItems(this.itemCriteria);
        },

        onPageChange(data) {
            this.page = data.page;
            this.limit = data.limit;

            this.getItems();
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
            this.itemRepository
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
                promises.push(this.itemRepository.delete(id, Shopware.Context.api));
            });

            this.selectedItems = {};

            Promise.all(promises).then(() => {
                this.getItems();
            });
        },

        onSaveItem() {
            this.isLoading = true;

            this.itemRepository
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
            if (item !== undefined) {
                this.selectedItem = item;
            } else {
                this.selectedItem = this.itemRepository.create(Shopware.Context.api);

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
