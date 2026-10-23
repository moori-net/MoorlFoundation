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
        },
        contextMenuItems: {
            type: Array,
            default: undefined,
        },
        filters: {
            type: Array,
            default: () => [],
        }
    },

    data() {
        return {
            selectedItems: {},
            pendingDeleteIds: [],
            isBulkDeleting: false,
            pendingBulkAction: null,
            isBulkEditing: false,
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
            editId: null,
            contextActionBusy: false
        };
    },

    computed: {
        bulkContextMenuItems() {
            if (!this.gridShowSelection) return [];
            return this.configuredContextMenuItems.filter(action =>
                action.allowBulkEdit === true && (!action.privilege || this.acl.can(action.privilege))
            );
        },

        gridShowSelection() {
            // The listing mixin also uses showSelection; local card attributes take precedence.
            return this.$attrs.showSelection
                ?? this.$attrs['show-selection']
                ?? MoorlFoundation.ModuleHelper.getByEntity(this.entity)?.componentConfig?.list?.showSelection
                ?? false;
        },

        canBulkDelete() {
            return this.gridShowSelection && this.allowDelete && this.acl.can(`${this.entity}:delete`);
        },

        configuredContextMenuItems() {
            return this.contextMenuItems
                ?? MoorlFoundation.ModuleHelper.getByEntity(this.entity)?.componentConfig?.list?.contextMenuItems
                ?? [];
        },

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

    watch: {
        defaultItem: { deep: true, handler: 'reloadFilteredItems' },
        filters: { deep: true, handler: 'reloadFilteredItems' },
    },

    methods: {
        async createdComponent() {
            await this.initListHelper();

            this.sortBy = this.listHelper.sortBy;
            this.sortDirection = this.listHelper.sortDirection;

            this.getItems();
        },

        async getItems() {
            // Existing row actions may request a reload; bulk execution reloads once at the end.
            if (this.isBulkEditing) return;
            this.selectedItems = {};
            this.pendingDeleteIds = [];
            this.pendingBulkAction = null;
            this.$refs?.grid?.resetSelection?.();
            const criteria = Shopware.Data.Criteria.fromCriteria(this.itemCriteria);
            this.filters.forEach(filter => criteria.addFilter(filter));
            await this.loadItems(criteria);
        },

        reloadFilteredItems() {
            if (!this.ready) return;
            this.page = 1;
            this.getItems();
        },

        getContextMenuItems(item) {
            return this.configuredContextMenuItems.filter(action =>
                (!action.privilege || this.acl.can(action.privilege)) &&
                (typeof action.visible !== 'function' || action.visible(item))
            );
        },

        isContextMenuItemDisabled(action, item) {
            return this.contextActionBusy || this.isBulkDeleting || (typeof action.disabled === 'function' ? action.disabled(item) : !!action.disabled);
        },

        onBulkContextMenuItem(action) {
            if (this.contextActionBusy || this.isBulkDeleting || this.isLoading || !this.bulkContextMenuItems.includes(action)) return;
            const items = Object.values(this.selectedItems ?? {});
            if (items.length > 0) this.pendingBulkAction = { action, items };
        },

        onCancelBulkAction() {
            this.pendingBulkAction = null;
        },

        async onConfirmBulkAction() {
            const pending = this.pendingBulkAction;
            const action = this.bulkContextMenuItems.find(action => action.name === pending?.action.name);
            if (!pending || !action || this.contextActionBusy || this.isBulkDeleting) return;
            this.pendingBulkAction = null;
            this.contextActionBusy = true;
            this.isBulkEditing = true;
            this.isLoading = true;
            try {
                for (const item of pending.items) {
                    try {
                        await action.action(item, this);
                    } catch (error) {
                        this.createNotificationError({ message: error.message });
                    }
                }
            } finally {
                this.isBulkEditing = false;
                try {
                    await this.getItems();
                } finally {
                    this.contextActionBusy = false;
                    this.isLoading = false;
                }
            }
        },

        async onContextMenuItem(action, item) {
            if (this.isContextMenuItemDisabled(action, item) ||
                (action.privilege && !this.acl.can(action.privilege))) return;
            this.contextActionBusy = true;
            try {
                await action.action(item, this);
            } catch (error) {
                this.createNotificationError({ message: error.message });
            } finally {
                this.contextActionBusy = false;
            }
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

        canDeleteItem(item) {
            const canDelete = MoorlFoundation.ModuleHelper.getByEntity(this.entity)?.componentConfig?.list?.canDelete;
            return this.allowDelete && this.acl.can(`${this.entity}:delete`)
                && (typeof canDelete !== 'function' || canDelete(item));
        },

        onDeleteItem(item) {
            if (!this.canDeleteItem(item)) return;
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
            if (!this.canBulkDelete || this.isBulkDeleting || this.contextActionBusy || this.isLoading) return;
            this.pendingDeleteIds = Object.keys(this.selectedItems ?? {});
        },

        onCancelBulkDelete() {
            this.pendingDeleteIds = [];
        },

        async onConfirmBulkDelete() {
            if (!this.canBulkDelete || this.isBulkDeleting || this.contextActionBusy || this.pendingDeleteIds.length === 0) return;
            const ids = [...this.pendingDeleteIds];
            this.pendingDeleteIds = [];
            this.isBulkDeleting = true;
            this.isLoading = true;

            try {
                const results = await Promise.allSettled(ids.map(async id =>
                    this.itemRepository.delete(id, Shopware.Context.api)
                ));
                results.filter(result => result.status === 'rejected').forEach(({ reason }) => {
                    this.createNotificationError({
                        message: reason?.response?.data?.errors?.[0]?.detail ?? reason?.message ?? String(reason),
                    });
                });
                await this.getItems();
            } finally {
                this.isBulkDeleting = false;
                this.isLoading = false;
            }
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
