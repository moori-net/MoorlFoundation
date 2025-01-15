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
        moorlFoundation() {
            return MoorlFoundation;
        },

        category() {
            if (this.element.data?.category?.name) {
                return this.element.data.category;
            }

            return {
                name: 'Lorem Ipsum dolor',
                description: `Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                          sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
                          sed diam voluptua.`.trim(),
                media: {
                    url: '/administration/static/img/cms/preview_glasses_large.jpg',
                    alt: 'Lorem Ipsum dolor'
                }
            };
        },

        product() {
            if (this.element.data?.product?.name) {
                return this.element.data.product;
            }

            return {
                name: 'Demo Product',
                description: `Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                          sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
                          sed diam voluptua.`.trim(),
                cover: {
                    media: {
                        url: '/administration/static/img/cms/preview_glasses_large.jpg',
                        alt: 'Add to cart'
                    }
                }
            };
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        mediaUrl() {
            const context = Shopware.Context.api;
            const elemData = this.element.data.media;

            if (!this.element.config.mediaActive.value) {
                if (this.element.config.elementType.value === 'category') {
                    if (this.category.media && this.category.media.id) {
                        return this.category.media.url;
                    }

                    return `${context.assetsPath}${this.category.media.url}`;
                }

                if (this.element.config.elementType.value === 'product') {
                    if (this.product.cover && this.product.cover.media) {
                        return this.product.cover.media.url;
                    }

                    return `${context.assetsPath}${this.product.cover.media.url}`;
                }
            }

            if (elemData && elemData.id) {
                return this.element.data.media.url;
            }

            if (elemData && elemData.url) {
                return `${context.assetsPath}${elemData.url}`;
            }

            if (this.element.config.videoActive.value) {
                return 'http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
            }

            return `${context.assetsPath}/administration/static/img/cms/preview_mountain_large.jpg`;
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
                    'background-image': 'url("' + this.mediaUrl + '")',
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

                this.$set(this.element.data, 'iconMediaId', mediaEntity === null ? null : mediaEntity.id);
                this.$set(this.element.data, 'iconMedia', mediaEntity);
            }

            if (this.element.config.media.value) {
                const mediaEntity2 = await this.mediaRepository.get(this.element.config.media.value, Shopware.Context.api);

                this.$set(this.element.data, 'mediaId', mediaEntity2 === null ? null : mediaEntity2.id);
                this.$set(this.element.data, 'media', mediaEntity2);
            }

            this.$emit('element-update', this.element);
        },
    }
});
