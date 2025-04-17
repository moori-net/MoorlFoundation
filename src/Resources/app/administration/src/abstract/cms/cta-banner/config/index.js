import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-cta-banner-config', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    inject: ['repositoryFactory'],

    data() {
        return {
            mediaModalIsOpen: false,
            mediaModalIndex: 'media',
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
            return `cms-element-media-config-${this.element.id}`;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-cta-banner');
            this.initElementData('moorl-cta-banner');
        },

        resetElementTypeConfig() {
            this.element.config.category.value = null;
            this.element.config.product.value = null;

            this.initElementData('moorl-cta-banner');
        },

        async onImageUpload({ targetId }, index) {
            const mediaEntity = await this.mediaRepository.get(
                targetId,
                Shopware.Context.api
            );

            this.element.config[index].value = mediaEntity.id;

            this.updateElementData(index, mediaEntity);
        },

        previewSource(index) {
            if (
                this.element.data &&
                this.element.data[index] &&
                this.element.data[index].id
            ) {
                return this.element.data[index];
            }

            return this.element.config[index].value;
        },

        onImageRemove(e, index) {
            this.element.config[index].value = null;

            this.updateElementData(index);
        },

        updateElementData(index, media = null) {
            this.element.data[index + 'Id'] = media === null ? null : media.id;
            this.element.data[index] = media;

            this.$emit('element-update', this.element);
        },

        onChangeCategory() {
            this.initElementData('moorl-cta-banner');
        },

        onChangeProduct() {
            this.initElementData('moorl-cta-banner');
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
                this.element.data[index + 'Id'] = mediaEntity[0].id;
                this.element.data[index] = mediaEntity[0];
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
        },
    },
});
