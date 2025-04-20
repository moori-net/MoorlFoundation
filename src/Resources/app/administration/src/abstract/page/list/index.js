const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-page-list', {
    template,

    inject: [
        'repositoryFactory',
        'filterFactory'
    ],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('listing')
    ],

    data() {
        return {
            entity: null,
            properties: ['active', 'name'],
            pluginName: null, // Demo button
            demoName: 'standard',
            snippetSrc: 'moorl-foundation',

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
        listHelper() {
            return new MoorlFoundation.ListHelper({
                componentName: this.componentName,
                entity: this.entity,
                properties: this.properties,
                tc: this.$tc,
                routerLink: this.getItemRoute('detail')
            });
        },

        mediaProperty() {
            return this.listHelper.getMediaProperty();
        },

        componentName() {
            return this.$options.name;
        },

        indexPage() {
            let name = this.$route.name;
            let parts = name.split(".");
            let currentPath = parts.pop();
            return currentPath === 'index';
        },

        dateFilter() {
            return Shopware.Filter.getByName('date');
        },

        itemRepository() {
            return this.repositoryFactory.create(this.entity);
        },

        itemCriteria() {
            const itemCriteria  = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'priority';

            itemCriteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sortBy => {
                itemCriteria.addSorting(Criteria.sort(sortBy, this.sortDirection, this.naturalSorting));
            });

            this.listHelper.getAssociations().forEach(association => {
                itemCriteria.addAssociation(association);
            });

            return itemCriteria ;
        },

        columns() {
            return this.listHelper.getColumns();
        }
    },

    methods: {
        async getList() {
            // This method is called by $route watcher of the listing mixin before created() is called!
            if (!this.entity) {
                console.error(`${this.componentName} has no entity`);
                return;
            }
            // Copies for listing mixin
            this.searchConfigEntity = this.entity;
            this.storeKey = `grid.filter.${this.entity}`;

            this.isLoading = true;

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

            try {
                const response = await this.itemRepository.search(criteria);

                this.total = response.total;
                this.items = response
                this.isLoading = false;
            } catch {
                this.isLoading = false;
            }
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
            if (item[this.mediaProperty].media !== undefined) {
                return item[this.mediaProperty].media
            }

            return item[this.mediaProperty];
        },
    }
});
