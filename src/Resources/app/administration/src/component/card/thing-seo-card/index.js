import template from './index.html.twig';

const {Component} = Shopware;

Component.register('moorl-thing-seo-card', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        item: {
            type: Object,
            required: true,
        }
    },

    data() {
        return {
            mediaModalIsOpen: false
        };
    },

    computed: {
        mediaRepository() {
            return this.repositoryFactory.create('media');
        }
    },

    methods: {
        setMediaItem({targetId}) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((updatedMedia) => {
                this.item.metaMediaId = targetId;
                this.item.metaMedia = updatedMedia;
            });
        },
        onDropMedia(dragData) {
            this.setMediaItem({targetId: dragData.id});
        },
        setMediaFromSidebar(mediaEntity) {
            this.item.metaMediaId = mediaEntity.id;
        },
        onUnlinkMedia() {
            this.item.metaMediaId = null;
        },
        onCloseModal() {
            this.mediaModalIsOpen = false;
        },
        onSelectionChanges(mediaEntity) {
            this.item.metaMediaId = mediaEntity[0].id;
            this.item.metaMedia = mediaEntity[0];
        },
        onOpenMediaModal() {
            this.mediaModalIsOpen = true;
        }
    }
});
