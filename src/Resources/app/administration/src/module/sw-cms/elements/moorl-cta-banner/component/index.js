const {Component, Mixin} = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-moorl-cta-banner', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: ['repositoryFactory'],

    computed: {
        bannerTitle() {
            const titleTag = this.element.config.titleTag.value;
            let bannerTitle = null;

            if (this.element.config.title.value) {
                bannerTitle = `<${titleTag}>${this.element.config.title.value}</${titleTag}>`;
            } else {
                if (this.element.config.elementType.value === 'category' && this.element.data.category) {
                    bannerTitle = `<${titleTag}>${this.element.data.category.translated.name}</${titleTag}>`;
                } else if (this.element.config.elementType.value === 'product' && this.element.data.product) {
                    bannerTitle = `<${titleTag}>${this.element.data.product.translated.name}</${titleTag}>`;
                }
            }

            return bannerTitle;
        },

        bannerDescription() {
            let bannerDescription = null;

            if (this.element.config.quote.value) {
                bannerDescription = this.element.config.quote.value;
            } else {
                if (this.element.config.elementType.value === 'category' && this.element.data.category) {
                    bannerDescription = this.element.data.category.description;
                } else if (this.element.config.elementType.value === 'product' && this.element.data.product) {
                    bannerDescription = this.element.data.product.description;
                }
            }

            return bannerDescription;
        },

        bannerMediaUrl() {
            const context = Shopware.Context.api;
            let bannerMediaUrl = context.assetsPath + '/administration/static/img/cms/preview_mountain_large.jpg';
            if (this.element.config.videoActive.value) {
                bannerMediaUrl = 'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
            }

            if (this.element.data?.media) {
                bannerMediaUrl = this.element.data.media.url;
            } else {
                if (this.element.config.elementType.value === 'category' && this.element.data?.category?.media) {
                    bannerMediaUrl = this.element.data.category.media.url;
                } else if (this.element.config.elementType.value === 'product' && this.element.data?.product?.cover?.media) {
                    bannerMediaUrl = this.element.data.product.cover.media.url;
                }
            }

            return bannerMediaUrl;
        },

        category() {
            if (!this.element.data || !this.element.data.category || !this.element.data.category.media) {
                return this.element?.defaultData?.category;
            }

            return this.element.data.category;
        },

        product() {
            if (!this.element.data || !this.element.data.product || !this.element.data.product.cover) {
                return this.element?.defaultData?.product;
            }

            return this.element.data.product;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        iconMediaUrl() {
            const context = Shopware.Context.api;
            const elemData = this.element.data.iconMedia;

            if (elemData && elemData.id) {
                return this.element.data.iconMedia.url;
            }
            if (elemData && elemData.url) {
                return `${context.assetsPath}${elemData.url}`;
            }

            return `${context.assetsPath}/administration/static/img/cms/preview_mountain_large.jpg`;
        },

        elementCss() {
            return {
                'min-height': this.element.config.height.value,
                'background': this.element.config.elementBackground.value
            }
        },

        backgroundCss() {
            if (!this.element.config.videoActive.value) {
                return {
                    'background-image': 'url("' + this.bannerMediaUrl + '")',
                    'background-attachment': this.element.config.backgroundFixed.value ? 'fixed' : 'initial',
                    'background-position': `${this.element.config.backgroundVerticalAlign.value} ${this.element.config.backgroundHorizontalAlign.value}`,
                    'background-size': this.element.config.backgroundDisplayMode.value === 'custom' ? `${this.element.config.backgroundSizeX.value} ${this.element.config.backgroundSizeY.value}` : this.element.config.backgroundDisplayMode.value
                }
            }
        },

        overlayCss() {
            return {
                'background': this.element.config.overlayBackground.value,
                'align-items': this.element.config.boxVerticalAlign.value,
                'justify-content': this.element.config.boxHorizontalAlign.value,
                'min-height': this.element.config.height.value,
                'height': '100%'
            }
        },

        boxCss() {
            return {
                'display': this.element.config.iconPosition.value === 'top' ? 'block' : 'flex',
                'margin': this.element.config.boxMargin.value,
                'padding': this.element.config.boxPadding.value,
                'background': this.element.config.boxBackground.value,
                'color': this.element.config.boxColor.value,
                'width': this.element.config.boxWidth.value,
                'height': this.element.config.boxHeight.value,
                'text-align': this.element.config.boxTextAlign.value,
                'border-radius': this.element.config.boxBorderRadius.value
            }
        },

        iconCss() {
            return {
                'font-size': this.element.config.iconFontSize.value,
                'margin-bottom': this.element.config.iconMarginBottom.value,
                'margin-right': this.element.config.iconMarginRight.value
            }
        },

        boxClass() {
            if (!this.element.config.boxMaxWidth.value) {
                return ['reset'];
            } else {
                return null;
            }
        }
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
        this.setMedia();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-cta-banner');
            this.initElementData('moorl-cta-banner');
        },

        titleHTML(title) {
            return `<${this.element.config.titleTag.value}>${title}</${this.element.config.titleTag.value}>`;
        },

        async setMedia() {
            if (this.element.config.iconMedia.value) {
                const mediaEntity = await this.mediaRepository.get(this.element.config.iconMedia.value, Shopware.Context.api);

                this.element.data.iconMediaId = mediaEntity === null ? null : mediaEntity.id;
                this.element.data.iconMedia = mediaEntity;
            }

            if (this.element.config.media.value) {
                const mediaEntity2 = await this.mediaRepository.get(this.element.config.media.value, Shopware.Context.api);

                this.element.data.mediaId = mediaEntity2 === null ? null : mediaEntity2.id;
                this.element.data.media = mediaEntity2;
            }

            this.$emit('element-update', this.element);
        },
    }
});
