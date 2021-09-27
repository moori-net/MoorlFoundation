const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Component.register('moorl-cms-element-config', {
    template,

    inject: [
        'repositoryFactory',
        'context',
        'numberRangeService'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            repository: null,
            items: null,
            sortBy: 'name',
            isLoading: true
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        cmsElementConfigRepository() {
            return this.repositoryFactory.create('moorl_cms_element_config');
        },

        columns() {
            return [{
                property: 'name',
                dataIndex: 'name',
                label: this.$t('moorl-foundation.properties.name'),
                inlineEdit: 'string'
            }, {
                property: 'type',
                dataIndex: 'type',
                label: this.$t('moorl-foundation.properties.type'),
                inlineEdit: 'string'
            }];
        }
    },

    created() {
        this.repository = this.cmsElementConfigRepository;
        this.getList();
    },

    methods: {
        getList() {
            const criteria = new Criteria(this.page, this.limit);
            //const params = this.getListingParams();
            const params = {};

            this.isLoading = true;
            this.naturalSorting = this.sortBy === 'name';
            this.sortDirection = params.sortDirection || 'ASC';

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            this.repository.search(criteria, Shopware.Context.api).then((items) => {
                this.total = items.total;
                this.isLoading = false;
                this.items = items;
                return items;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        updateSelection() {
        },

        updateTotal({total}) {
            this.total = total;
        },

        onRefresh() {
            this.getList();
        }
    }
});
