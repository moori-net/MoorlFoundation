const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-page--list', {
    template,

    inject: [
        'repositoryFactory',
        'filterFactory'
    ],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('listing'),
        Shopware.Mixin.getByName('placeholder')
    ],

    data() {
        return {
            items: null,
            selectedItems: null,
            sortBy: 'name',
            sortDirection: 'ASC',
            filterCriteria: [],
            naturalSorting: false,
            showImportModal: false,
            showExportModal: false,
            isLoading: true,
            storeKey: 'grid.filter.moorl_merchant',
            activeFilterNumber: 0,
            searchConfigEntity: 'moorl_merchant',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('moorl_merchant');
        },

        defaultCriteria() {
            const defaultCriteria  = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'priority';

            defaultCriteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sortBy => {
                defaultCriteria.addSorting(Criteria.sort(sortBy, this.sortDirection, this.naturalSorting));
            });

            this.filterCriteria.forEach(filter => {
                defaultCriteria.addFilter(filter);
            });

            return defaultCriteria ;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        productManufacturerRepository() {
            return this.repositoryFactory.create('product_manufacturer');
        },

        categoryRepository() {
            return this.repositoryFactory.create('category');
        },

        customerGroupRepository() {
            return this.repositoryFactory.create('customer_group');
        },

        countryRepository() {
            return this.repositoryFactory.create('country');
        },

        tagRepository() {
            return this.repositoryFactory.create('tag');
        },

        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

        customFieldSetRepository() {
            return this.repositoryFactory.create('custom_field_set');
        },

        columns() {
            return [{
                property: 'active',
                dataIndex: 'active',
                label: this.$tc('moorl-foundation.properties.active'),
                inlineEdit: 'boolean',
                allowResize: true,
                align: 'center'
            }, {
                property: 'name',
                dataIndex: 'name',
                label: this.$tc('moorl-foundation.properties.name'),
                routerLink: 'moorl.merchant.finder.detail',
                inlineEdit: 'string',
                allowResize: true,
                primary: true
            }, {
                property: 'type',
                dataIndex: 'type',
                label: this.$tc('moorl-foundation.properties.type'),
                inlineEdit: 'string',
                align: 'center',
                allowResize: true
            },{
                property: 'company',
                dataIndex: 'company',
                label: this.$tc('moorl-foundation.properties.company'),
                routerLink: 'moorl.merchant.finder.detail',
                inlineEdit: 'string',
                allowResize: true
            }, {
                property: 'countryCode',
                dataIndex: 'countryCode',
                label: this.$tc('moorl-foundation.properties.zipcode'),
                inlineEdit: 'string',
                allowResize: true
            }, {
                property: 'city',
                dataIndex: 'city',
                label: this.$tc('moorl-foundation.properties.city'),
                inlineEdit: 'string',
                allowResize: true
            }, {
                property: 'email',
                dataIndex: 'email',
                label: this.$tc('moorl-foundation.properties.email'),
                inlineEdit: 'string',
                allowResize: true
            }, {
                property: 'locationLon',
                dataIndex: 'locationLon',
                label: this.$tc('moorl-foundation.properties.location'),
                allowResize: true,
                align: 'center'
            }, {
                property: 'priority',
                dataIndex: 'priority',
                label: this.$tc('moorl-foundation.properties.priority'),
                inlineEdit: 'number',
                allowResize: true,
                align: 'right'
            }];
        }
    },

    methods: {
        async getList() {
            this.isLoading = true;

            const criteria = await this.addQueryScores(this.term, this.defaultCriteria);

            if (!this.entitySearchable) {
                this.isLoading = false;
                this.total = 0;

                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            return this.repository.search(criteria)
                .then(searchResult => {
                    this.items = searchResult;
                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },

        updateSelection(selection) {

            this.selectedItems = selection;
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
        }
    }
});
