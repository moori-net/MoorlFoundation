const { Component, Mixin } = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-appflix-cta-banner', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: ['repositoryFactory'],

    data() {
        return {
            mediaModalIsOpen: false,
            mediaModalIndex: 'media',
            initialFolderId: null,
            snippetPrefix: 'sw-cms.elements.appflix-cta-banner.',
        };
    },

    computed: {
        elementTypeOptions() {
            return [
                {
                    value: 'custom',
                    label: `${this.snippetPrefix}label.custom`
                },
                {
                    value: 'category',
                    label: `${this.snippetPrefix}label.category`
                },
                {
                    value: 'cta',
                    label: `${this.snippetPrefix}label.cta`
                },
                {
                    value: 'product',
                    label: `${this.snippetPrefix}label.product`
                },
            ];
        },

        backgroundSizeOptions() {
            return [
                {
                    value: 'cover',
                    label: `${this.snippetPrefix}label.cover`
                },
                {
                    value: 'contain',
                    label: `${this.snippetPrefix}label.contain`
                },
                {
                    value: 'custom',
                    label: `${this.snippetPrefix}label.custom`
                }
            ];
        },

        iconTypeOptions() {
            return [
                {
                    value: 'none',
                    label: `${this.snippetPrefix}label.none`
                },
                {
                    value: 'fa',
                    label: `${this.snippetPrefix}label.fa`
                },
                {
                    value: 'svg',
                    label: `${this.snippetPrefix}label.svg`
                },
                {
                    value: 'media',
                    label: `${this.snippetPrefix}label.media`
                },
            ];
        },

        iconPositionOptions() {
            return [
                {
                    value: 'top',
                    label: `${this.snippetPrefix}label.top`
                },
                {
                    value: 'left',
                    label: `${this.snippetPrefix}label.left`
                },
            ];
        },

        verticalAlignOptions() {
            return [
                {
                    value: 'flex-start',
                    label: `${this.snippetPrefix}label.top`
                },
                {
                    value: 'center',
                    label: `${this.snippetPrefix}label.center`
                },
                {
                    value: 'flex-end',
                    label: `${this.snippetPrefix}label.bottom`
                }
            ];
        },

        horizontalAlignOptions() {
            return [
                {
                    value: 'flex-start',
                    label: `${this.snippetPrefix}label.left`
                },
                {
                    value: 'center',
                    label: `${this.snippetPrefix}label.center`
                },
                {
                    value: 'flex-end',
                    label: `${this.snippetPrefix}label.right`
                }
            ];
        },

        textAlignOptions() {
            return [
                {
                    value: 'left',
                    label: `${this.snippetPrefix}label.left`
                },
                {
                    value: 'center',
                    label: `${this.snippetPrefix}label.center`
                },
                {
                    value: 'right',
                    label: `${this.snippetPrefix}label.right`
                }
            ];
        },

        verticalTextAlignOptions() {
            return [
                {
                    value: 'top',
                    label: `${this.snippetPrefix}label.top`
                },
                {
                    value: 'center',
                    label: `${this.snippetPrefix}label.center`
                },
                {
                    value: 'bottom',
                    label: `${this.snippetPrefix}label.bottom`
                }
            ];
        },

        mediaHoverOptions() {
            return [
                {
                    value: 'none',
                    label: `${this.snippetPrefix}label.none`
                },
                {
                    value: 'zoom',
                    label: `${this.snippetPrefix}label.zoom`
                },
                {
                    value: 'rotate',
                    label: `${this.snippetPrefix}label.rotate`
                },
                {
                    value: 'rotate-zoom',
                    label: `${this.snippetPrefix}label.rotateZoom`
                },
                {
                    value: 'colorize',
                    label: `${this.snippetPrefix}label.colorize`
                },
                {
                    value: 'colorize-zoom',
                    label: `${this.snippetPrefix}label.colorizeZoom`
                },
                {
                    value: 'colorize-blur',
                    label: `${this.snippetPrefix}label.colorizeBlur`
                },
                {
                    value: 'blur',
                    label: `${this.snippetPrefix}label.blur`
                },
                {
                    value: 'blur-zoom',
                    label: `${this.snippetPrefix}label.blurZoom`
                }
            ];
        },

        moorlFoundation() {
            return MoorlFoundation;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        uploadTag() {
            return `cms-element-media-config-${this.element.id}`;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('appflix-cta-banner');
            this.initElementData('appflix-cta-banner');
        },

        async onImageUpload({ targetId }, index) {
            const mediaEntity = await this.mediaRepository.get(targetId, Shopware.Context.api);

            this.element.config[index].value = mediaEntity.id;

            this.updateElementData(index, mediaEntity);
        },

        previewSource(index) {
            if (this.element.data && this.element.data[index] && this.element.data[index].id) {
                return this.element.data[index];
            }

            return this.element.config[index].value;
        },

        onImageRemove(e, index) {
            this.element.config[index].value = null;

            this.updateElementData(index);
        },

        updateElementData(index, media = null) {
            this.$set(this.element.data, index + 'Id', media === null ? null : media.id);
            this.$set(this.element.data, index, media);

            this.$emit('element-update', this.element);
        },

        onChangeCategory() {
            this.initElementData('appflix-cta-banner');
        },

        onChangeProduct() {
            this.initElementData('appflix-cta-banner');
        },

        onBlur(content) {
            this.emitChanges(content);
        },

        onInput(content) {
            this.emitChanges(content);
        },

        emitChanges(content) {
            if (content !== this.element.config.content.value) {
                this.element.config.content.value = content;
                this.$emit('element-update', this.element);
            }
        },

        onCloseModal() {
            this.mediaModalIsOpen = false;
        },

        onSelectionChanges(mediaEntity) {
            let index = this.mediaModalIndex;

            this.element.config[index].value = mediaEntity[0].id;

            if (this.element.data) {
                this.$set(this.element.data, index + 'Id', mediaEntity[0].id);
                this.$set(this.element.data, index, mediaEntity[0]);
            }

            this.$emit('element-update', this.element);
        },

        onOpenMediaModal(index) {
            this.mediaModalIndex = index;
            this.mediaModalIsOpen = true;
        },

        onChangeMinHeight(value) {
            this.element.config.minHeight.value = value === null ? '' : value;

            this.$emit('element-update', this.element);
        },

        onChangeDisplayMode(value) {
            if (value === 'cover') {
                this.element.config.verticalAlign.value = '';
            } else {
                this.element.config.minHeight.value = '';
            }

            this.$emit('element-update', this.element);
        }
    }
});
