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
            elementName: 'moorl-foundation-listing',
            items: null
        };
    },

    computed: {
        listingCss() {
            if (this.element.config.listingLayout.value === 'grid' || this.element.config.listingLayout.value === 'standard') {
                return {
                    'grid-template-columns': `repeat(auto-fit, minmax(${this.element.config.itemWidth.value}, 1fr))`,
                    'grid-auto-rows': this.element.config.itemHeight.value,
                    'grid-gap': this.element.config.gapSize.value
                }
            }
            if (this.element.config.listingLayout.value === 'list') {
                return {
                    'grid-gap': this.element.config.gapSize.value,
                    'grid-auto-rows': this.element.config.itemHeight.value,
                }
            }
            if (this.element.config.listingLayout.value === 'slider') {
                return {
                    'grid-gap': this.element.config.gapSize.value,
                    'height': this.element.config.itemHeight.value,
                }
            }
        },

        listingClass() {
            return `moorl-listing-${this.element.config.listingLayout.value}`;
        },

        imageClass() {
            return `is-${this.element.config.displayMode.value}`;
        },

        itemCss() {
            return {
                'padding': this.element.config.itemPadding.value,
                'background-color': this.element.config.itemBackgroundColor.value,
                'border': this.element.config.itemHasBorder.value ? '1px solid #333' : null,
                'border-radius': this.element.config.itemHasBorder.value ? '6px' : null,
            }
        },

        itemClass() {
            return `moorl-listing-item-${this.element.config.itemLayout.value}`;
        },

        contentCss() {
            return {
                'padding': this.element.config.contentPadding.value,
                'background-color': this.element.config.contentBackgroundColor.value,
                'color': this.element.config.contentColor.value,
                'text-align': this.element.config.textAlign.value,
            }
        },

        defaultCriteria() {
            const criteria = new Criteria();
            criteria.setLimit(12);

            return criteria;
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
        },

        itemTitle(item) {
            return item.title;
        },

        itemDescription(item) {
            return item.teaser;
        },
    }
});
