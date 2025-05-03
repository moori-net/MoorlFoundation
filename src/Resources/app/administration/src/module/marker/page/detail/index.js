import template from './index.html.twig';

const utils = Shopware.Utils;

Shopware.Component.override('moorl-marker-detail', {
    template,

    data() {
        return {
            entity: 'moorl_marker',
            uploadTagMarker: utils.createId(),
            uploadTagMarkerRetina: utils.createId(),
            uploadTagMarkerShadow: utils.createId(),
        };
    },

    computed: {
        locations() {
            const ms = this.item.markerSettings;

            return [
                {
                    entityId: this.item.id,
                    latlng: [52.5173, 13.402],
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
                    popup: '<p><b>Lorem Ipsum GmbH</b><br>Musterstraße 1<br>12345 Musterstadt</p>',
                },
            ];
        },
    },

    methods: {
        onItemLoaded() {
            this.item.markerSettings ??= {};
        },

        openMediaSidebar() {
            this.$refs.mediaSidebarItem.openContent();
        },

        // Marker
        setMarkerItem({ targetId }) {
            this.mediaRepository
                .get(targetId, Shopware.Context.api)
                .then((updatedMedia) => {
                    this.item.markerId = targetId;
                    this.item.marker = updatedMedia;
                });
        },
        onDropMarker(dragData) {
            this.setMarkerItem({ targetId: dragData.id });
        },
        setMarkerFromSidebar(mediaEntity) {
            this.item.markerId = mediaEntity.id;
        },
        onUnlinkMarker() {
            this.item.markerId = null;
        },

        // Marker Shadow
        setMarkerRetinaItem({ targetId }) {
            this.mediaRepository
                .get(targetId, Shopware.Context.api)
                .then((updatedMedia) => {
                    this.item.markerRetinaId = targetId;
                    this.item.markerRetina = updatedMedia;
                });
        },
        onDropMarkerRetina(dragData) {
            this.setMarkerRetinaItem({ targetId: dragData.id });
        },
        setMarkerRetinaFromSidebar(mediaEntity) {
            this.item.markerRetinaId = mediaEntity.id;
        },
        onUnlinkMarkerRetina() {
            this.item.markerRetinaId = null;
        },

        // Marker Shadow
        setMarkerShadowItem({ targetId }) {
            this.mediaRepository
                .get(targetId, Shopware.Context.api)
                .then((updatedMedia) => {
                    this.item.markerShadowId = targetId;
                    this.item.markerShadow = updatedMedia;
                });
        },
        onDropMarkerShadow(dragData) {
            this.setMarkerShadowItem({ targetId: dragData.id });
        },
        setMarkerShadowFromSidebar(mediaEntity) {
            this.item.markerShadowId = mediaEntity.id;
        },
        onUnlinkMarkerShadow() {
            this.item.markerShadowId = null;
        },
    }
});
