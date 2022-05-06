const { Component, Mixin } = Shopware;

import template from './index.html.twig';

Component.register('sw-cms-el-config-appflix-search-hero', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: ['repositoryFactory'],

    data() {
        return {
            mediaModalIsOpen: false,
            initialFolderId: null,
            snippetPrefix: 'sw-cms.elements.appflix-search-hero.',
        };
    },

    computed: {

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        uploadTag() {
            return 'cms-element-media-config-${this.element.id}';
        },

        previewSource() {
            if (this.element.data && this.element.data.media && this.element.data.media.id) {
                return this.element.data.media;
            }

            return this.element.config.media.value;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('appflix-search-hero');
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
