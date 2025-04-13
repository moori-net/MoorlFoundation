import template from './index.html.twig';
import './index.scss';

const {Component, Mixin} = Shopware;
const {isEmpty} = Shopware.Utils.types;

Component.register('moorl-media-gallery', {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        item: {
            type: Object,
            required: true
        },
        entity: {
            type: String,
            required: true
        },
        defaultFolder: {
            type: String,
            required: false,
            default: "product"
        }
    },

    data() {
        return {
            showCoverLabel: true,
            showMediaModal: false,
            isMediaLoading: false,
            columnCount: 5,
            columnWidth: 90,
        };
    },

    computed: {
        currentItem() {
            return this.item;
        },

        mediaItems() {
            const mediaItems = this.itemMedia.slice();
            const placeholderCount = this.getPlaceholderCount(this.columnCount);

            if (placeholderCount === 0) {
                return mediaItems;
            }

            for (let i = 0; i < placeholderCount; i += 1) {
                mediaItems.push(this.createPlaceholderMedia(mediaItems));
            }
            return mediaItems;
        },

        cover() {
            if (!this.currentItem?.media) {
                return null;
            }
            const coverId = this.currentItem.cover ? this.currentItem.cover.mediaId : this.currentItem.coverId;
            return this.currentItem.media.find(media => media.id === coverId);
        },

        isLoading() {
            return this.isMediaLoading || this.isStoreLoading;
        },

        itemMediaRepository() {
            return this.repositoryFactory.create(this.entity);
        },

        itemMedia() {
            if (!this.currentItem?.media) {
                return [];
            }
            return this.currentItem.media;
        },

        itemMediaStore() {
            return this.currentItem.getAssociation('media');
        },

        gridAutoRows() {
            return `grid-auto-rows: ${this.columnWidth}`;
        }
    },

    methods: {
        onOpenMediaModal() {
            this.showMediaModal = true;
        },

        onCloseMediaModal() {
            this.showMediaModal = false;
        },

        onAddMedia(media) {
            if (isEmpty(media)) {
                return;
            }
            media.forEach((item) => {
                this.addMedia(item).catch(({fileName}) => {
                    this.createNotificationError({
                        message: this.$tc('sw-product.mediaForm.errorMediaItemDuplicated', 0, {fileName}),
                    });
                });
            });
        },

        addMedia(media) {
            if (this.isExistingMedia(media)) {
                return Promise.reject(media);
            }
            const newMedia = this.itemMediaRepository.create(Shopware.Context.api);
            newMedia.mediaId = media.id;
            newMedia.media = {
                url: media.url,
                id: media.id,
            };
            if (isEmpty(this.itemMedia)) {
                this.markMediaAsCover(newMedia);
            }
            this.itemMedia.add(newMedia);
            return Promise.resolve();
        },

        isExistingMedia(media) {
            return this.itemMedia.some(({id, mediaId}) => {
                return id === media.id || mediaId === media.id;
            });
        },

        onMediaUploadButtonOpenSidebar() {
            this.$root.$emit('sidebar-toggle-open');
        },

        updateColumnCount() {
            this.$nextTick(() => {
                if (this.isLoading) {
                    return false;
                }

                const cssColumns = window.getComputedStyle(this.$refs.grid, null)
                    .getPropertyValue('grid-template-columns')
                    .split(' ');
                this.columnCount = cssColumns.length;
                this.columnWidth = cssColumns[0];

                return true;
            });
        },

        getPlaceholderCount(columnCount) {
            if (this.itemMedia.length + 3 < columnCount * 2) {
                columnCount *= 2;
            }

            let placeholderCount = columnCount;

            if (this.itemMedia.length !== 0) {
                placeholderCount = columnCount - ((this.itemMedia.length) % columnCount);
                if (placeholderCount === columnCount) {
                    return 0;
                }
            }

            return placeholderCount;
        },

        createPlaceholderMedia(mediaItems) {
            return {
                isPlaceholder: true,
                isCover: mediaItems.length === 0,
                media: {
                    isPlaceholder: true,
                    name: '',
                },
                mediaId: mediaItems.length.toString(),
            };
        },

        buildProductMedia(mediaId) {
            this.isLoading = true;

            const itemMedia = this.itemMediaStore.create();
            itemMedia.mediaId = mediaId;

            if (this.itemMedia.length === 0) {
                itemMedia.position = 0;
                this.currentItem.cover = itemMedia;
                this.currentItem.coverId = itemMedia.id;
            } else {
                itemMedia.position = this.itemMedia.length + 1;
            }
            this.isLoading = false;

            return itemMedia;
        },

        successfulUpload({ targetId }) {
            // on replace
            if (this.currentItem.media.find((itemMedia) => itemMedia.mediaId === targetId)) {
                return;
            }

            const itemMedia = this.createMediaAssociation(targetId);
            this.currentItem.media.add(itemMedia);
        },

        createMediaAssociation(targetId) {
            const itemMedia = this.itemMediaRepository.create();

            itemMedia.appflixAdId = this.currentItem.id;
            itemMedia.mediaId = targetId;

            if (this.currentItem.media.length <= 0) {
                itemMedia.position = 0;
                this.currentItem.coverId = itemMedia.id;
            } else {
                itemMedia.position = this.currentItem.media.length;
            }
            return itemMedia;
        },

        onUploadFailed(uploadTask) {
            const toRemove = this.currentItem.media.find((itemMedia) => {
                return itemMedia.mediaId === uploadTask.targetId;
            });
            if (toRemove) {
                if (this.currentItem.coverId === toRemove.id) {
                    this.currentItem.coverId = null;
                }
                this.currentItem.media.remove(toRemove.id);
            }
            this.currentItem.isLoading = false;
        },

        removeCover() {
            this.currentItem.cover = null;
            this.currentItem.coverId = null;
        },

        isCover(itemMedia) {
            const coverId = this.currentItem.cover ? this.currentItem.cover.id : this.currentItem.coverId;

            if (this.currentItem.media.length === 0 || itemMedia.isPlaceholder) {
                return false;
            }

            return itemMedia.id === coverId;
        },

        removeFile(itemMedia) {
            if (this.currentItem.coverId === itemMedia.id) {
                this.currentItem.cover = null;
                this.currentItem.coverId = null;
            }

            if (this.currentItem.coverId === null && this.currentItem.media.length > 0) {
                this.currentItem.coverId = this.currentItem.media.first().id;
            }

            this.currentItem.media.remove(itemMedia.id);
        },

        markMediaAsCover(itemMedia) {
            this.currentItem.cover = itemMedia;
            this.currentItem.coverId = itemMedia.id;
        },

        onMediaItemDragSort(dragData, dropData, validDrop) {
            if (validDrop !== true) {
                return;
            }

            this.currentItem.media.moveItem(dragData.position, dropData.position);
            this.updateMediaItemPositions();
        },

        updateMediaItemPositions() {
            this.itemMedia.forEach((medium, index) => {
                medium.position = index;
            });
        },
    },
});
