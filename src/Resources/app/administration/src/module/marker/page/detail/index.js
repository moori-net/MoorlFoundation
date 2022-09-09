import template from './index.html.twig';

const {Component, Mixin, Context} = Shopware;
const {Criteria} = Shopware.Data;
const utils = Shopware.Utils;

Component.register('moorl-marker-detail', {
    template,

    inject: [
        'repositoryFactory',
        'context',
        'foundationApiService'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            item: null,
            isLoading: false,
            processSuccess: false,
            uploadTagMarker: utils.createId(),
            uploadTagMarkerRetina: utils.createId(),
            uploadTagMarkerShadow: utils.createId()
        };
    },

    computed: {
        locations() {
            const ms = this.item.markerSettings

            return [
                {
                    entityId: this.item.id,
                    latlng: [52.5173, 13.4020],
                    icon: {
                        svg: this.item.svg,
                        iconUrl: this.item.marker?.url,
                        iconRetinaUrl: this.item.markerRetina?.url,
                        shadowUrl: this.item.markerShadow?.url,
                        iconSize: [ms.iconSizeX, ms.iconSizeY],
                        iconAnchor: [ms.iconAnchorX, ms.iconAnchorY],
                        popupAnchor: [ms.popupAnchorX, ms.popupAnchorY],
                        shadowSize: [ms.shadowSizeX, ms.shadowSizeY],
                        shadowAnchor: [ms.shadowAnchorX, ms.shadowAnchorY],
                    },
                    popup: '<p><b>Lorem Ipsum GmbH</b><br>Musterstraße 1<br>12345 Musterstadt</p>'
                }
            ];
        },

        repository() {
            return this.repositoryFactory.create('moorl_marker');
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        defaultCriteria() {
            return new Criteria();
        }
    },

    created() {
        this.getItem();
    },

    methods: {
        getItem() {
            this.repository
                .get(this.$route.params.id, Context.api, this.defaultCriteria)
                .then((entity) => {
                    this.item = entity;

                    if (!this.item.markerSettings) {
                        this.item.markerSettings = {};
                    }
                });
        },

        onClickSave() {
            this.isLoading = true;
            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                    this.processSuccess = true;
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('moorl-foundation.notification.errorTitle'),
                        message: exception
                    });
                });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        openMediaSidebar() {
            this.$refs.mediaSidebarItem.openContent();
        },

        // Marker
        setMarkerItem({targetId}) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((updatedMedia) => {
                this.item.markerId = targetId;
                this.item.marker = updatedMedia;
            });
        },
        onDropMarker(dragData) {
            this.setMarkerItem({targetId: dragData.id});
        },
        setMarkerFromSidebar(mediaEntity) {
            this.item.markerId = mediaEntity.id;
        },
        onUnlinkMarker() {
            this.item.markerId = null;
        },

        // Marker Shadow
        setMarkerRetinaItem({targetId}) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((updatedMedia) => {
                this.item.markerRetinaId = targetId;
                this.item.markerRetina = updatedMedia;
            });
        },
        onDropMarkerRetina(dragData) {
            this.setMarkerRetinaItem({targetId: dragData.id});
        },
        setMarkerRetinaFromSidebar(mediaEntity) {
            this.item.markerRetinaId = mediaEntity.id;
        },
        onUnlinkMarkerRetina() {
            this.item.markerRetinaId = null;
        },

        // Marker Shadow
        setMarkerShadowItem({targetId}) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((updatedMedia) => {
                this.item.markerShadowId = targetId;
                this.item.markerShadow = updatedMedia;
            });
        },
        onDropMarkerShadow(dragData) {
            this.setMarkerShadowItem({targetId: dragData.id});
        },
        setMarkerShadowFromSidebar(mediaEntity) {
            this.item.markerShadowId = mediaEntity.id;
        },
        onUnlinkMarkerShadow() {
            this.item.markerShadowId = null;
        }
    }
});
