import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-entity-grid-v2', {
    inject: ['acl'],

    emits: ['customAction'],

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
        minVisibility: {
            type: Number,
            required: false,
            default: 0,
        },
        customAction: {
            type: String,
            required: false,
            default: undefined,
        }
    },

    data() {
        return {
            selectedItems: null,
            page: 1,
            limit: 10,
            term: null,
            isLoading: true,
            sortBy: 'createdAt',
            sortDirection: 'ASC',
            showEditModal: false,
            showImportModal: false,
            showExportModal: false,
            deleteId: null,
            customActionId: null,
            editId: null
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

        onSaveItem(item) {
            return this.itemRepository.save(item, Shopware.Context.api).then(() => {
                return this.onSearch();
            });
        },

        onEditItem(editId) {
            this.editId = editId;
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

            this.onSearch();
        },

        onCustomActionId() {
            this.$emit('customAction', this.customActionId);
            this.customActionId = null;
        }
    },
});
