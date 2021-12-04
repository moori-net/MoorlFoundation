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
        'repositoryFactory'
    ],

    data() {
        return {
            entity: null,
            criteria: new Criteria(1, 12),
            entityCollection: [],
            elementName: null,
            configWhitelist: null
        };
    },

    computed: {
        moorlFoundation() {
            return MoorlFoundation;
        },

        sortingCriteria() {
            const criteria = new Criteria
            criteria.addFilter(Criteria.equals('entity', this.entity));
            return criteria;
        },

        elementOptions() {
            const options = {
                listingSource: [
                    {value: 'static', label: 'sw-cms.elements.moorl-foundation-listing.listingSource.static'},
                    {value: 'select', label: 'sw-cms.elements.moorl-foundation-listing.listingSource.select'},
                    {value: 'auto', label: 'sw-cms.elements.moorl-foundation-listing.listingSource.auto'},
                ],
                listingLayout: [
                    {value: 'grid', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.grid'},
                    {value: 'list', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.list'},
                    {value: 'standard', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.standard'},
                    {value: 'slider', label: 'sw-cms.elements.moorl-foundation-listing.listingLayout.slider'}
                ],
                itemLayout: [
                    {value: 'overlay', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.overlay'},
                    {value: 'image-or-title', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.image-or-title'},
                    {value: 'image-content', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.image-content'},
                    {value: 'content-image', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.content-image'},
                    {value: 'standard', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.standard'},
                    {value: 'custom', label: 'sw-cms.elements.moorl-foundation-listing.itemLayout.custom'}
                ],
                displayMode: [
                    {value: 'cover', label: 'sw-cms.elements.moorl-foundation-listing.displayMode.cover'},
                    {value: 'contain', label: 'sw-cms.elements.moorl-foundation-listing.displayMode.contain'},
                    {value: 'standard', label: 'sw-cms.elements.moorl-foundation-listing.displayMode.standard'}
                ],
                textAlign: [
                    {value: 'left', label: 'sw-cms.elements.moorl-foundation-listing.textAlign.left'},
                    {value: 'center', label: 'sw-cms.elements.moorl-foundation-listing.textAlign.center'},
                    {value: 'right', label: 'sw-cms.elements.moorl-foundation-listing.textAlign.right'}
                ],
                mode: [
                    {value: 'carousel', label: 'sw-cms.elements.moorl-foundation-listing.mode.carousel'},
                    {value: 'gallery', label: 'sw-cms.elements.moorl-foundation-listing.mode.gallery'}
                ],
                navigationArrows: [
                    {value: null, label: 'sw-cms.elements.moorl-foundation-listing.none'},
                    {value: 'outside', label: 'sw-cms.elements.moorl-foundation-listing.navigationArrows.outside'},
                    {value: 'inside', label: 'sw-cms.elements.moorl-foundation-listing.navigationArrows.inside'}
                ],
                navigationDots: [
                    {value: null, label: 'sw-cms.elements.moorl-foundation-listing.none'},
                    {value: 'outside', label: 'sw-cms.elements.moorl-foundation-listing.navigationDots.outside'},
                    {value: 'inside', label: 'sw-cms.elements.moorl-foundation-listing.navigationDots.inside'}
                ]
            };

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
                    this.$set(this.element.data, 'listingItems', result);
                });
        },

        onSelectionChange() {
            this.element.config.listingItemIds.value = this.entityCollection.getIds();
            this.getList();
        },
    }
});
