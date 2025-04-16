import template from './index.html.twig';

Shopware.Component.register('moorl-location-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        },
    },

    computed: {
        locations() {
            const ms = this.item.marker?.markerSettings;

            return [
                {
                    entityId: this.item.id,
                    latlng: [this.item.locationLat, this.item.locationLon],
                    icon: ms
                        ? {
                              svg: this.item.marker.svg,
                              iconUrl: this.item.marker.marker?.url,
                              iconRetinaUrl: this.item.marker.markerRetina?.url,
                              shadowUrl: this.item.marker.markerShadow?.url,
                              iconSize: [ms.iconSizeX, ms.iconSizeY],
                              iconAnchor: [ms.iconAnchorX, ms.iconAnchorY],
                              popupAnchor: [ms.popupAnchorX, ms.popupAnchorY],
                              shadowSize: [ms.shadowSizeX, ms.shadowSizeY],
                              shadowAnchor: [
                                  ms.shadowAnchorX,
                                  ms.shadowAnchorY,
                              ],
                          }
                        : null,
                    popup: '<p><b>Lorem Ipsum GmbH</b><br>Musterstraße 1<br>12345 Musterstadt</p>',
                },
            ];
        },

        repository() {
            return this.repositoryFactory.create('moorl_marker');
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },
    },
});
