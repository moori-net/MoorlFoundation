import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-page-list', {
    template,

    inject: ['filterFactory'],

    mixins: [
        Shopware.Mixin.getByName('listing'),
        Shopware.Mixin.getByName('moorl-listing'),
    ],

    data() {
        return {
            entity: null,
            pluginName: null, // Demo button
            demoName: 'standard',
            items: null,
            showImportModal: false,
            showExportModal: false,
            isLoading: true,
            activeFilterNumber: 0,
            sortBy: 'name',
            filterCriteria: []
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        indexPage() {
            let name = this.$route.name;
            let parts = name.split(".");
            let currentPath = parts.pop();
            return currentPath === 'index';
        }
    },

    methods: {
        async getList() {
            this.isLoading = true;

            await this.initListHelper();

            // Copies for listing mixin
            this.searchConfigEntity = this.entity;
            this.storeKey = `grid.filter.${this.entity}`;

            let criteria = await Shopware.Service('filterService').mergeWithStoredFilters(this.storeKey, this.itemCriteria);

            criteria = await this.addQueryScores(this.term, criteria);

            if (!this.entitySearchable) {
                this.total = 0;
                this.isLoading = false;

                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            await this.loadItems(criteria);
        },

        getItemRoute(target) {
            if (this.indexPage) {
                return undefined;
            }

            let name = this.$route.name;
            let parts = name.split(".");
            parts.pop();
            parts.push(target ?? 'detail');

            return parts.join(".");
        },

        onCreateItem() {
            this.$router.push({
                name: this.getItemRoute('create')
            });
        },

        async onDuplicate(reference) {
            const behavior = {
                cloneChildren: true,
                overwrites: {
                    name: `${reference.name} [${this.$tc('global.default.duplicate')}]`,
                    locked: false
                }
            };

            const duplicate = await this.itemRepository.clone(reference.id, behavior, Shopware.Context.api);

            if (this.indexPage) {
                this.getList();

                return Promise.resolve();
            }

            await this.$router.push({
                name: this.getItemRoute('detail'),
                params:{id: duplicate.id}
            });

            return Promise.resolve();
        },

        updateSelection(selection) {
            this.selection = selection;
        },

        updateTotal({total}) {
            this.total = total;
        },

        onCloseModal() {
            this.showImportModal = false;
            this.showExportModal = false;
            this.showModal = false;
        },

        onRefresh() {
            this.getList();
        },

        onChangeLanguage() {
            this.getList();
        },

        onImportModal() {
            this.showImportModal = true;
        },

        onExportModal() {
            this.showExportModal = true;
        },

        previewMediaSource(item) {
            if (item[this.mediaProperty]?.media !== undefined) {
                return item[this.mediaProperty].media
            }

            return item[this.mediaProperty];
        },
    }
});
