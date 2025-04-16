import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-layout-card', {
    template,

    inject: ['acl', 'feature'],

    props: {
        item: {
            type: Object,
            required: true,
        },

        cmsPage: {
            type: Object,
            required: false,
            default: null,
        },

        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },

        pageTypes: {
            type: Array,
            required: false,
            default() {
                return [];
            },
        },

        headline: {
            type: String,
            required: false,
            default: '',
        },
    },

    data() {
        return {
            showLayoutSelectionModal: false,
        };
    },

    computed: {
        cmsPageTypes() {
            return {
                page: this.$tc('sw-cms.detail.label.pageTypeShopPage'),
                landingpage: this.$tc(
                    'sw-cms.detail.label.pageTypeLandingpage'
                ),
                product_list: this.$tc('sw-cms.detail.label.pageTypeCategory'),
                product_detail: this.$tc('sw-cms.detail.label.pageTypeProduct'),
                creator_detail: this.$tc('moorl-creator.general.creator'),
                magazine_article_detail: this.$tc(
                    'moorl-magazine.general.article'
                ),
            };
        },
    },

    methods: {
        onLayoutSelect(selectedLayout) {
            this.item.cmsPageId = selectedLayout;
        },

        onLayoutReset() {
            this.onLayoutSelect(null);
        },

        openInPagebuilder() {
            if (!this.cmsPage) {
                this.$router.push({ name: 'sw.cms.create' });
            } else {
                this.$router.push({
                    name: 'sw.cms.detail',
                    params: { id: this.item.cmsPageId },
                });
            }
        },

        openLayoutModal() {
            this.showLayoutSelectionModal = true;
        },

        closeLayoutModal() {
            this.showLayoutSelectionModal = false;
        },
    },
});
