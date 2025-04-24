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
            const layout = this.getValue('listingLayout');

            const common = {
                'grid-gap': this.getValue('gapSize'),
            };

            if (layout === 'grid' || layout === 'standard') {
                return {
                    ...common,
                    'grid-template-columns': `repeat(auto-fit, minmax(${this.getValue('itemWidth')}, 1fr))`,
                    'grid-auto-rows': this.getValue('itemHeight'),
                };
            }

            if (layout === 'list') {
                return {
                    ...common,
                    'grid-auto-rows': this.getValue('itemHeight'),
                };
            }

            if (layout === 'slider') {
                return {
                    ...common,
                    height: this.getValue('itemHeight'),
                };
            }

            return {};
        },

        listingClass() {
            return `moorl-listing-${this.getValue('listingLayout')}`;
        },

        imageClass() {
            return `is-${this.getValue('displayMode')}`;
        },

        imageCss() {
            if (this.getValue('itemLayout') === 'avatar') {
                return {
                    width: this.getValue('itemWidth'),
                    height: this.getValue('itemWidth'),
                };
            }
            return {};
        },

        itemCss() {
            return {
                padding: this.getValue('itemPadding'),
                'background-color': this.getValue('itemBackgroundColor'),
                border: this.getValue('itemHasBorder') ? '1px solid #333' : null,
                'border-radius': this.getValue('itemHasBorder') ? '6px' : null,
                '--content-color': this.getValue('contentColor'),
                '--content-background-color': this.getValue('contentBackgroundColor'),
                '--content-highlight-color': this.getValue('contentHighlightColor'),
            };
        },

        itemClass() {
            return `moorl-listing-item-${this.getValue('itemLayout')}`;
        },

        contentCss() {
            return {
                padding: this.getValue('contentPadding'),
                'background-color': this.getValue('contentBackgroundColor'),
                color: this.getValue('contentColor'),
                'text-align': this.getValue('textAlign'),
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
        'element.config.listingSource.value': 'getList',
        'element.config.listingItemIds.value': 'getList',
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            const elementType = this.elementType;
            this.cmsElementEntity = this.cmsElements[elementType].cmsElementEntity;
            this.cmsElementMapping = this.cmsElements[elementType].cmsElementMapping;

            this.getList();
        },

        getList() {
            this.repository
                .search(this.defaultCriteria, Shopware.Context.api)
                .then(result => {
                    this.element.data.listingItems = result;
                    this.isLoading = false;
                });
        },

        itemTitle(item) {
            return MoorlFoundation.CmsElementHelper.getItemData(item, 'name');
        },

        itemMedia(item) {
            return MoorlFoundation.CmsElementHelper.getItemData(item, 'media');
        },

        itemDescription(item) {
            return MoorlFoundation.CmsElementHelper.getItemData(item, 'description');
        },

        getValue(key) {
            return this.element.config?.[key]?.value ?? null;
        }
    }
});
