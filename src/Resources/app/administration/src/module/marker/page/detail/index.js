import template from './index.html.twig';
import L from "leaflet";

const { Component, Mixin, StateDeprecated, Context } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;
const { mapApiErrors } = Shopware.Component.getComponentHelper();
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
            mapItem: null,
            markerItem: null,
            markerItems: null,
            coord: [52.5173, 13.4020],
            isLoading: false,
            processSuccess: false,
            uploadTagMarker: utils.createId(),
            uploadTagMarkerRetina: utils.createId(),
            uploadTagMarkerShadow: utils.createId()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('moorl_marker');
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        defaultCriteria() {
            const criteria = new Criteria();
            return criteria;
        },

        searchCriteria() {
            const criteria = new Criteria();
            return criteria;
        },

        searchContext() {
            return {
                ...Context.api,
                inheritance: true
            };
        },

        markerSettings() {
            return {
                iconSizeX: 25,
                iconSizeY: 41,
                shadowSizeX: 41,
                shadowSizeY: 41,
                iconAnchorX: 12,
                iconAnchorY: 41,
                shadowAnchorX: 11,
                shadowAnchorY: 41,
                popupAnchorX: 1,
                popupAnchorY: -34
            };
        }
    },

    created() {
        this.getItem();
    },

    mounted() {
        const that = this;
        setTimeout(function () {
            that.drawMap();
        }, 3000);
    },

    methods: {
        getItem() {
            this.repository
                .get(this.$route.params.id, this.searchContext, this.defaultCriteria)
                .then((entity) => {
                    this.item = entity;
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
                }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$t('moorl-foundation.notification.errorTitle'),
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
        },

        drawMap() {
            this.mapItem = L.map(this.$refs['embedMap'], {
                center: this.coord,
                zoom: 16
            });

            L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
            }).addTo(this.mapItem);
        },

        markerPreview() {
            let iconOptions = {
                shadowUrl: this.item.markerShadow ? this.item.markerShadow.url : null,
                iconRetinaUrl: this.item.markerRetina ? this.item.markerRetina.url : null,
                iconUrl: this.item.marker ? this.item.marker.url : null,
                iconSize: [
                    this.item.markerSettings.iconSizeX,
                    this.item.markerSettings.iconSizeY
                ],
                shadowSize: [
                    this.item.markerSettings.shadowSizeX,
                    this.item.markerSettings.shadowSizeY
                ],
                iconAnchor: [
                    this.item.markerSettings.iconAnchorX,
                    this.item.markerSettings.iconAnchorY
                ],
                shadowAnchor: [
                    this.item.markerSettings.shadowAnchorX,
                    this.item.markerSettings.shadowAnchorY
                ],
                popupAnchor: [
                    this.item.markerSettings.popupAnchorX,
                    this.item.markerSettings.popupAnchorY
                ]
            };

            console.log(iconOptions);

            const featureMarker = [];

            featureMarker.push(
                L.marker(this.coord, { icon: L.icon(iconOptions) })
                    .bindPopup('<p><b>Lorem Ipsum GmbH</b><br>Musterstraße 1<br>12345 Musterstadt</p>', {
                        autoPan: false,
                        autoClose: true
                    })
                    .on('click', function () {
                        this.markerItems.eachLayer(function (layer) {
                            if (!layer.getPopup().isOpen()) {
                                layer.openPopup();
                            }
                        });
                    })
            );

            if (this.markerItems) {
                this.markerItems.clearLayers();
            }

            this.markerItems = L.layerGroup(featureMarker).addTo(this.mapItem);
        }
    }
});
