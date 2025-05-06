import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-media-field', {
    template,

    emits: [
        'update:value',
        'update:media'
    ],

    inject: ['repositoryFactory'],

    props: {
        value: {
            type: String,
            required: true,
            default: null
        },
        media: {
            type: Object,
            required: false,
            default: null
        },
        entity: {
            type: String,
            required: true,
            default: 'product'
        }
    },

    data() {
        return {
            showMediaModal: false
        };
    },

    computed: {
        currentValue: {
            get() {
                return this.currentMedia ?? this.value;
            },
            set(newValue) {
                this.$emit('update:value', newValue ?? null);
            },
        },

        currentMedia: {
            get() {
                return this.media;
            },
            set(newValue) {
                this.$emit('update:media', newValue ?? null);
            },
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        uploadTag() {
            return `${this.entity}_${Shopware.Utils.createId()}`;
        }
    },

    methods: {
        onOpenMediaModal() {
            this.showMediaModal = true;
        },

        onCloseMediaModal() {
            this.showMediaModal = false;
        },

        onSetMediaItem({targetId}) {
            this.mediaRepository.get(targetId).then((updatedMedia) => {
                this.currentValue = targetId;
                this.currentMedia = updatedMedia;
            });
        },

        onUnlinkMedia() {
            this.currentValue = null;
            this.currentMedia = null;
        },

        onMediaDropped(dropItem) {
            this.onSetMediaItem({ targetId: dropItem.id });
        },

        onMediaSelectionChange(mediaItems) {
            const media = mediaItems[0];
            if (!media) {
                return;
            }

            this.onSetMediaItem({targetId: media.id});
        },
    },
});
