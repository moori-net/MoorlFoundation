const { Criteria } = Shopware.Data;

import template from './index.html.twig';

Shopware.Component.register('moorl-cms-element-config-index', {
    template,

    inject: ['repositoryFactory', 'context', 'numberRangeService'],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('listing'),
        Shopware.Mixin.getByName('placeholder'),
    ],

    data() {
        return {
            repository: null,
            items: null,
            sortBy: 'name',
            isLoading: true,
            showImportModal: false,
            selectedFile: null,
            isImporting: false,
            locale: null,
            naturalSorting: true,
            sortDirection: 'DESC',
            showDeleteModal: false,
            filterLoading: false,
            filterCriteria: [],
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        cmsElementConfigRepository() {
            return this.repositoryFactory.create('moorl_cms_element_config');
        },

        columns() {
            return [
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$tc('moorl-foundation.properties.name'),
                    inlineEdit: 'string',
                },
                {
                    property: 'type',
                    dataIndex: 'type',
                    label: this.$tc('moorl-foundation.properties.type'),
                    inlineEdit: 'string',
                },
            ];
        },

        defaultCriteria() {
            const defaultCriteria = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'name';
            defaultCriteria.setTerm(this.term);
            this.sortBy.split(',').forEach((sortBy) => {
                defaultCriteria.addSorting(
                    Criteria.sort(
                        sortBy,
                        this.sortDirection,
                        this.naturalSorting
                    )
                );
            });

            this.filterCriteria.forEach((filter) => {
                defaultCriteria.addFilter(filter);
            });

            return defaultCriteria;
        },
    },

    created() {
        this.repository = this.cmsElementConfigRepository;
        this.getList();
    },

    methods: {
        async getList() {
            this.isLoading = true;

            try {
                const items = await this.repository.search(
                    this.defaultCriteria,
                    Shopware.Context.api
                );

                this.total = items.total;
                this.items = items;
                this.selection = {};
                this.isLoading = false;
            } catch {
                this.isLoading = false;
            }
        },

        updateSelection() {},

        updateTotal({ total }) {
            this.total = total;
        },

        onRefresh() {
            this.getList();
        },
    },
});
