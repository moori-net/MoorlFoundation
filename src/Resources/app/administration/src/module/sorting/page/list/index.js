const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Shopware.Component.register('moorl-sorting-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Shopware.Mixin.getByName('listing')
    ],

    data() {
        return {
            isLoading: false,
            items: null,
            sortBy: 'entity'
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('moorl_sorting');
        },

        criteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sorting => {
                criteria.addSorting(Criteria.sort(sorting, this.sortDirection, this.naturalSorting));
            });

            return criteria;
        },

        columns() {
            return [
                {
                    property: 'active',
                    dataIndex: 'active',
                    label: this.$tc('moorl-sorting.properties.active'),
                    inlineEdit: 'boolean',
                    align: 'center'
                },
                {
                    property: 'entity',
                    dataIndex: 'entity',
                    label: this.$tc('moorl-sorting.properties.entity'),
                    routerLink: 'moorl.sorting.detail'
                },
                {
                    property: 'label',
                    dataIndex: 'label',
                    label: this.$tc('moorl-sorting.properties.label'),
                    routerLink: 'moorl.sorting.detail',
                    inlineEdit: 'string'
                },
                {
                    property: 'priority',
                    dataIndex: 'priority',
                    label: this.$tc('moorl-sorting.properties.priority'),
                    inlineEdit: 'number'
                }
            ]
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getList();
        },

        getList() {
            this.isLoading = true;

            const context = {...Shopware.Context.api, inheritance: true};
            return this.repository.search(this.criteria, context).then((result) => {
                this.total = result.total;
                this.items = result;
                this.isLoading = false;
            });
        },

        onDelete(option) {
            this.$refs.listing.deleteItem(option);

            this.repository.search(this.criteria, {...Shopware.Context.api, inheritance: true}).then((result) => {
                this.total = result.total;
                this.items = result;
            });
        },

        changeLanguage() {
            this.getList();
        }
    }
});
