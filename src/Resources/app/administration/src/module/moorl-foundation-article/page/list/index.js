const { Application, Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

import template from './index.html.twig';

Component.register('moorl-foundation-article-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            deleteId: null,
            showBulkDeleteModal: false,
            isBulkLoading: false,
            page: 1,
            limit: 10,
            total: null,
            records: null,
            criteria: new Criteria()
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('moorl_foundation_article');
        }
    },

    methods: {
        doSearch() {
            this.loading = true;
            return this.repository.search(this.criteria, Shopware.Context.api).then(this.applyResult);
        },

        applyResult(result) {
            this.records = result;
            this.total = result.total;
            this.page = result.criteria.page;
            this.limit = result.criteria.limit;
            this.loading = false;

            //this.$emit('update-records', result);
        },

        paginate({ page = 1, limit = 10 }) {

            console.log(page);

            this.criteria.setPage(page);
            this.criteria.setLimit(limit);

            return this.doSearch();
        },

        deleteItems() {
            this.criteria.setPage(1);

            this.repository.search(new Criteria(), Shopware.Context.api).then((items) => {
                const promises = [];

                items.forEach((item) => {
                    promises.push(this.repository.delete(item.id, Shopware.Context.api));
                });

                return Promise.all(promises).then(() => {
                    return this.doSearch();
                }).catch(() => {
                    return this.doSearch();
                });
            });
        },
    },

    created() {
        this.criteria.addSorting(Criteria.sort('date', 'DESC'));
        this.criteria.setPage(1);
        this.criteria.setLimit(10);

        this.doSearch();
    }
});
