const {Criteria} = Shopware.Data;

Shopware.Mixin.register('moorl-listing', {
    inject: ['repositoryFactory'],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder'),
    ],

    data() {
        return {
            items: [],
            isLoading: true,
            sortBy: 'createdAt',
            sortDirection: 'ASC',
            naturalSorting: undefined,
            page: 1,
            limit: 10,
            total: 10,
            term: null,
            listHelper: null,
            ready: false,
            showImportModal: false,
            showExportModal: false
        };
    },

    computed: {
        itemRepository() {
            return this.repositoryFactory.create(this.entity);
        },

        itemCriteria() {
            const criteria  = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'priority';

            criteria.resetSorting();
            this.sortBy.split(',').forEach(sortBy => {
                criteria.addSorting(Criteria.sort(sortBy, this.sortDirection, this.naturalSorting));
            });

            criteria.setTotalCountMode(1);
            criteria.setTerm(this.term);

            this.listHelper.getAssociations().forEach(association => {
                criteria.addAssociation(association);
            });

            if (this.defaultItem !== undefined) {
                for (const [field, value] of Object.entries(this.defaultItem)) {
                    criteria.addFilter(Criteria.equals(field, value));
                }
            }

            return criteria;
        },

        currencyRepository() {
            return this.repositoryFactory.create('currency');
        },

        columns() {
            return this.listHelper.columns;
        },

        mediaProperty() {
            return this.listHelper.mediaProperty;
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

        currencyFilter() {
            return Shopware.Filter.getByName('currency');
        },
    },

    methods: {
        async initListHelper() {
            if (this.listHelper) {
                return Promise.resolve();
            }

            const currencies = await this.currencyRepository.search(new Criteria());

            this.listHelper = new MoorlFoundation.ListHelper({
                componentName: this.componentName,
                entity: this.entity,
                currencies,
                tc: this.$tc
            });

            await this.listHelper.ready;

            this.sortBy = this.sortBy ?? this.listHelper.getSortBy();
            this.ready = true;
        },

        async loadItems(finalCriteria) {
            this.isLoading = true;

            try {
                const result = await this.itemRepository.search(finalCriteria);
                this.items = result;
                this.total = result.total;
            } catch (e) {
                this.createNotificationError({ message: e.message });
            } finally {
                this.isLoading = false;
            }
        },

        getCurrencyPriceByCurrencyId(currencyId, prices) {
            const priceForItem = prices.find((price) => price.currencyId === currencyId);

            if (priceForItem) {
                return priceForItem;
            }

            return {
                currencyId: null,
                gross: null,
                linked: true,
                net: null,
            };
        },

        onImportModal() {
            this.showImportModal = true;
        },

        onExportModal() {
            this.showExportModal = true;
        },

        onCloseModal() {
            this.showImportModal = false;
            this.showExportModal = false;
        },

        onRefresh() {
            this.getList();
        },

        onChangeLanguage() {
            this.getList();
        }
    }
});
