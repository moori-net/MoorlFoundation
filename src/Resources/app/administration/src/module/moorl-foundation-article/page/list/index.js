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
            article: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repo() {
            return this.repositoryFactory.create('moorl_foundation_article');
        },

        columns() {
            return [
                {
                    property: 'date',
                    dataIndex: 'date',
                    label: this.$t('moorl-foundation.properties.date'),
                    align: 'center'
                },
                {
                    property: 'title',
                    dataIndex: 'title',
                    label: this.$t('moorl-foundation.properties.title'),
                    routerLink: 'moorl.foundation.article.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true
                },
                {
                    property: 'teaser',
                    dataIndex: 'teaser',
                    label: this.$t('moorl-foundation.properties.teaser'),
                    allowResize: true,
                }
            ]
        }
    },

    methods: {
        getList() {
            const criteria = new Criteria();
            criteria.addSorting(Criteria.sort('date', 'DESC'));

            this.repo
                .search(criteria, Shopware.Context.api)
                .then((result) => {
                    this.article = result;
                });
        }
    },

    created() {
        this.getList();
    }
});
