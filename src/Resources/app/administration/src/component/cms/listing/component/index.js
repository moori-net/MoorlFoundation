const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-moorl-foundation-listing', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            entity: 'moorl_magazine_article',
            elementName: 'moorl-magazine',
            defaultCriteria: null,
        };
    },

    computed: {
        listingCss() {
        },

        itemCss() {
        },

        defaultCriteria() {
            return new Criteria();
        },

        repository() {
            return this.repositoryFactory.create(this.entity);
        },
    },

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig(this.elementName);
            this.initElementData(this.elementName);
            this.getList();
        },

        getList() {
            this.repository
                .search(this.defaultCriteria, Shopware.Context.api)
                .then((result) => {
                    this.items = result;
                });
        }
    }
});
