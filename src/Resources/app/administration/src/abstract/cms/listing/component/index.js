const { Criteria } = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-listing', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    inject: ['repositoryFactory'],

    data() {
        return {
            cmsElementMapping: null,
            cmsElementEntity: null,
            isLoading: true,
            items: [],
            criteria: new Criteria(1, 12),
            elementName: null,
        };
    },

    computed: {
        elementType() {
            return this.element.type;
        },

        listingCss() {
            if (
                this.element.config.listingLayout.value === 'grid' ||
                this.element.config.listingLayout.value === 'standard'
            ) {
                return {
                    'grid-template-columns': `repeat(auto-fit, minmax(${this.element.config.itemWidth.value}, 1fr))`,
                    'grid-auto-rows': this.element.config.itemHeight.value,
                    'grid-gap': this.element.config.gapSize.value,
                };
            }
            if (this.element.config.listingLayout.value === 'list') {
                return {
                    'grid-gap': this.element.config.gapSize.value,
                    'grid-auto-rows': this.element.config.itemHeight.value,
                };
            }
            if (this.element.config.listingLayout.value === 'slider') {
                return {
                    'grid-gap': this.element.config.gapSize.value,
                    height: this.element.config.itemHeight.value,
                };
            }
        },

        listingClass() {
            return `moorl-listing-${this.element.config.listingLayout.value}`;
        },

        imageClass() {
            return `is-${this.element.config.displayMode.value}`;
        },

        imageCss() {
            if (this.element.config.itemLayout.value === 'avatar') {
                return {
                    width: this.element.config.itemWidth.value,
                    height: this.element.config.itemWidth.value,
                };
            }
        },

        itemCss() {
            return {
                padding: this.element.config.itemPadding.value,
                'background-color':
                    this.element.config.itemBackgroundColor.value,
                border: this.element.config.itemHasBorder.value
                    ? '1px solid #333'
                    : null,
                'border-radius': this.element.config.itemHasBorder.value
                    ? '6px'
                    : null,
                '--content-color': this.element.config.contentColor.value,
                '--content-background-color':
                    this.element.config.contentBackgroundColor.value,
                '--content-highlight-color':
                    this.element.config.contentHighlightColor.value,
            };
        },

        itemClass() {
            return `moorl-listing-item-${this.element.config.itemLayout.value}`;
        },

        contentCss() {
            return {
                padding: this.element.config.contentPadding.value,
                'background-color':
                    this.element.config.contentBackgroundColor.value,
                color: this.element.config.contentColor.value,
                'text-align': this.element.config.textAlign.value,
            };
        },

        repository() {
            return this.repositoryFactory.create(this.cmsElementEntity.entity);
        },

        defaultCriteria() {
            const criteria = this.cmsElementEntity.criteria;

            criteria.setLimit(this.getValue('limit'));
            criteria.setIds([]);

            if (this.getValue('listingSource') === 'select') {
                criteria.setIds(this.getValue('listingItemIds'));
            }

            return criteria;
        },
    },

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            },
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.cmsElementEntity = this.cmsElements[this.elementType].cmsElementEntity;
            this.cmsElementMapping = this.cmsElements[this.elementType].cmsElementMapping;

            console.log(this.cmsElementEntity);
            console.log(this.cmsElementMapping);

            this.getList();
        },

        getList() {
            this.repository
                .search(this.defaultCriteria, Shopware.Context.api)
                .then((result) => {
                    this.items = result;
                    this.element.data.listingItems = result;

                    this.isLoading = false;
                });
        },

        itemTitle(item) {
            return item.title;
        },

        itemMedia(item) {
            return item.media;
        },

        itemDescription(item) {
            return item.teaser;
        },

        getValue(key) {
            return this.element.config?.[key]?.value ?? null;
        }
    },
});
