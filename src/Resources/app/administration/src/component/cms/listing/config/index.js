const {Component, Mixin} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-moorl-foundation-listing', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: [
        'repositoryFactory',
        'cmsService'
    ],

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

        elementOptions() {
            const options = {
                foreignKey: []
            };

            if (this.entityForeignKeys) {
                for (let value of this.entityForeignKeys.string) {
                    if (value.match(/\./g).length > 1) {
                        continue;
                    }
                    if (value.lastIndexOf("Id") === -1) {
                        continue;
                    }
                    options.foreignKey.push({
                        value: value,
                        label: value
                    });
                }

                Object.values(this.entityForeignKeys.entity).forEach(entity => {
                    for (let value of entity) {
                        if (value.match(/\./g).length > 1) {
                            continue;
                        }
                        options.foreignKey.push({
                            value: value + '.id',
                            label: value + '.id'
                        });
                    }
                });
            }

            if (this.configWhitelist) {
                for (const [key, whitelist] of Object.entries(this.configWhitelist)) {
                    options[key] = options[key].filter(
                        option => whitelist.includes(option.value)
                    );
                }

                return options;
            }

            return options;
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
        },

        entityForeignKeys() {
            return this.cmsService.getEntityMappingTypes(this.entity);
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
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
                    //this.element.data.listingItems = result;
                });
        },

        onSelectionChange() {
            this.element.config.listingItemIds.value = this.entityCollection.getIds();
            this.getList();
        },
    }
});
