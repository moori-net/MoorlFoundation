const { Component, Mixin } = Shopware;

import template from './index.html.twig';

Component.register('sw-cms-el-config-moorl-hero-banner', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: ['repositoryFactory'],

    data() {
        return {
            mediaModalIsOpen: false,
            initialFolderId: null
        };
    },

    computed: {
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        uploadTag() {
            return this.element.id
        },

        previewSource() {
            if (this.element.data && this.element.data.media && this.element.data.media.id) {
                return this.element.data.media;
            }

            return this.element.config.media.value;
        },

        flexAlignOptions() {
            return [
                {
                    value: 'flex-start',
                    label: `sw-cms.elements.moorl-cta-banner.label.start`
                },
                {
                    value: 'center',
                    label: `sw-cms.elements.moorl-cta-banner.label.center`
                },
                {
                    value: 'flex-end',
                    label: `sw-cms.elements.moorl-cta-banner.label.end`
                }
            ];
        },

        textAlignOptions() {
            return [
                {
                    value: 'left',
                    label: `sw-cms.elements.moorl-cta-banner.label.left`
                },
                {
                    value: 'center',
                    label: `sw-cms.elements.moorl-cta-banner.label.center`
                },
                {
                    value: 'right',
                    label: `sw-cms.elements.moorl-cta-banner.label.right`
                }
            ];
        },

        verticalTextAlignOptions() {
            return [
                {
                    value: 'top',
                    label: `sw-cms.elements.moorl-cta-banner.label.top`
                },
                {
                    value: 'center',
                    label: `sw-cms.elements.moorl-cta-banner.label.center`
                },
                {
                    value: 'bottom',
                    label: `sw-cms.elements.moorl-cta-banner.label.bottom`
                }
            ];
        },

        backgroundSizeOptions() {
            return [
                {
                    value: 'cover',
                    label: `sw-cms.elements.moorl-cta-banner.label.cover`
                },
                {
                    value: 'contain',
                    label: `sw-cms.elements.moorl-cta-banner.label.contain`
                },
                {
                    value: 'custom',
                    label: `sw-cms.elements.moorl-cta-banner.label.custom`
                }
            ];
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-hero-banner');
        },

        onChangeMedia() {
            return this.uploadStore.runUploads(this.uploadTag);
        },

        async onImageUpload({ targetId }) {
            const mediaEntity = await this.mediaRepository.get(targetId, Shopware.Context.api);

            this.element.config.media.value = mediaEntity.id;

            this.updateElementData(mediaEntity);
        },

        onImageRemove() {
            this.element.config.media.value = null;

            this.updateElementData();
        },

        updateElementData(media = null) {
            this.$set(this.element.data, 'mediaId', media === null ? null : media.id);
            this.$set(this.element.data, 'media', media);

            this.$emit('element-update', this.element);
        },

        onCloseModal() {
            this.mediaModalIsOpen = false;
        },

        onSelectionChanges(mediaEntity) {
            this.element.config.media.value = mediaEntity[0].id;

            if (this.element.data) {
                this.$set(this.element.data, 'mediaId', mediaEntity[0].id);
                this.$set(this.element.data, 'media', mediaEntity[0]);
            }

            this.$emit('element-update', this.element);
        },

        onOpenMediaModal() {
            this.mediaModalIsOpen = true;
        }
    }
});
