const {Component, Mixin} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-moorl-foundation-listing', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: ['repositoryFactory'],

    data() {
        return {
            entity: null,
            criteria: new Criteria(1, 12),
            entityCollection: [],
            elementName: null,
            configWhitelist: null,
            contentRoute: null
        };
    },

    computed: {
        sortingCriteria() {
            const criteria = new Criteria
            criteria.addFilter(Criteria.equals('entity', this.entity));
            criteria.addFilter(Criteria.equals('active', 1));
            return criteria;
        },

        repository() {
            return this.repositoryFactory.create(this.entity);
        },

        defaultCriteria() {
            this.criteria.setLimit(this.element.config.limit.value);
            if (this.element.config.limit.value > 23) {
                this.criteria.setLimit(24);
            }
            this.criteria.setIds([]);
            if (this.element.config.listingSource.value === 'select') {
                this.criteria.setIds(this.element.config.listingItemIds.value);
            }

            return this.criteria;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        getSelectFilter(key) {
            if (this.configWhitelist && this.configWhitelist[key] !== undefined) {
                return this.configWhitelist[key];
            }
            return [];
        },

        createdComponent() {
            if (this.elementName) {
                this.initElementConfig(this.elementName);
                this.initElementData(this.elementName);
            }
            this.initElementConfig('moorl-foundation-listing');
            this.initEntityCollection();
        },

        initEntityCollection() {
            this.entityCollection = new EntityCollection(
                '/' + this.entity.replace(/_/g,'-'),
                this.entity,
                Shopware.Context.api
            );

            if (this.element.config.listingSource.value !== 'select') {
                return;
            }

            if (this.element.config.listingItemIds.value.length <= 0) {
                return;
            }

            this.repository
                .search(this.defaultCriteria, Shopware.Context.api)
                .then((result) => {
                    this.entityCollection = result;
                });
        },

        getList() {
            this.repository
                .search(this.defaultCriteria, Shopware.Context.api)
                .then((result) => {
                    this.element.data.listingItems = result;
                });
        },

        onSelectionChange() {
            this.element.config.listingItemIds.value = this.entityCollection.getIds();
            this.getList();
        },
    }
});
