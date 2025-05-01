import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-config-moorl-hero-banner', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    inject: ['repositoryFactory'],

    data() {
        return {
            mediaModalIsOpen: false,
            initialFolderId: null,
        };
    },

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        uploadTag() {
            return this.element.id;
        },

        previewSource() {
            if (
                this.element.data &&
                this.element.data.media &&
                this.element.data.media.id
            ) {
                return this.element.data.media;
            }

            return this.element.config.media.value;
        },

        flexAlignOptions() {
            return [
                {
                    value: 'flex-start',
                    label: `moorl-foundation.field.start`,
                },
                {
                    value: 'center',
                    label: `moorl-foundation.field.center`,
                },
                {
                    value: 'flex-end',
                    label: `moorl-foundation.field.end`,
                },
            ];
        },

        textAlignOptions() {
            return [
                {
                    value: 'left',
                    label: `moorl-foundation.field.left`,
                },
                {
                    value: 'center',
                    label: `moorl-foundation.field.center`,
                },
                {
                    value: 'right',
                    label: `moorl-foundation.field.right`,
                },
            ];
        },

        verticalTextAlignOptions() {
            return [
                {
                    value: 'top',
                    label: `moorl-foundation.field.top`,
                },
                {
                    value: 'center',
                    label: `moorl-foundation.field.center`,
                },
                {
                    value: 'bottom',
                    label: `moorl-foundation.field.bottom`,
                },
            ];
        },

        backgroundSizeOptions() {
            return [
                {
                    value: 'cover',
                    label: `moorl-foundation.field.cover`,
                },
                {
                    value: 'contain',
                    label: `moorl-foundation.field.contain`,
                },
                {
                    value: 'custom',
                    label: `moorl-foundation.field.custom`,
                },
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
            const mediaEntity = await this.mediaRepository.get(
                targetId,
                Shopware.Context.api
            );

            this.element.config.media.value = mediaEntity.id;

            this.updateElementData(mediaEntity);
        },

        onImageRemove() {
            this.element.config.media.value = null;

            this.updateElementData();
        },

        updateElementData(media = null) {
            this.element.data.mediaId = media === null ? null : media.id;
            this.element.data.media = media;

            this.$emit('element-update', this.element);
        },

        onCloseModal() {
            this.mediaModalIsOpen = false;
        },

        onSelectionChanges(mediaEntity) {
            this.element.config.media.value = mediaEntity[0].id;

            if (this.element.data) {
                this.element.data.mediaId = mediaEntity[0].id;
                this.element.data.media = mediaEntity[0];
            }

            this.$emit('element-update', this.element);
        },

        onOpenMediaModal() {
            this.mediaModalIsOpen = true;
        },
    },
});
