import template from './index.html.twig';
import './index.scss';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

Shopware.Component.register('moorl-location', {
    template,

    props: {
        locations: {
            type: Array,
            required: false,
            default: [],
        },
        item: {
            type: Object,
            required: false,
            default: null,
        },
        tileLayer: {
            type: String,
            required: false,
            default: '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        },
        attribution: {
            type: String,
            required: false,
            default:
                'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        },
        options: {
            type: Array,
            required: false,
            default: ['dragging', 'tap'],
        },
        showOrder: { type: Boolean, required: false, default: true },
        label: { type: String, required: false, default: null },
        name: { type: String, required: false, default: null },
    },

    watch: {
        currentLocations() {
            this.initLocations();
        },
    },

    data() {
        return {
            _mapInstance: null,
            _mapElement: null,
        };
    },

    computed: {
        mapOptions() {
            return {
                scrollWheelZoom: this.options.includes('scrollWheelZoom'),
                dragging: this.options.includes('dragging'),
                tap: this.options.includes('tap')
            };
        },

        currentLocations() {
            const items = [...this.locations];

            if (this.item) {
                const m = this.item.marker;
                const ms = m?.markerSettings ?? {};

                const icon = m ? {
                    svg: m.svg,
                    iconUrl: m.marker?.url,
                    iconRetinaUrl: m.markerRetina?.url,
                    shadowUrl: m.markerShadow?.url,
                    iconSize: [ms.iconSizeX, ms.iconSizeY],
                    iconAnchor: [ms.iconAnchorX, ms.iconAnchorY],
                    popupAnchor: [ms.popupAnchorX, ms.popupAnchorY],
                    shadowSize: [ms.shadowSizeX, ms.shadowSizeY],
                    shadowAnchor: [ms.shadowAnchorX, ms.shadowAnchorY],
                } : {};

                items.push({
                    entityId: this.item.id,
                    latlng: [
                        this.item.locationLat ?? 52.5173,
                        this.item.locationLon ?? 13.402
                    ],
                    icon,
                    popup: `<p><b>${this.item.name}</b><br>${this.item.street}<br>${this.item.zipcode} ${this.item.city}</p>`,
                });
            }

            return items;
        }
    },

    mounted() {
        this.initMap();
    },

    methods: {
        initMap() {
            if (this._mapInstance) {
                return;
            }

            if (!this.$refs['moorlLocation']) {
                return;
            }

            this._mapInstance = {
                layerGroup: L.layerGroup([]),
                map: L.map(this.$refs['moorlLocation'], this.mapOptions)
            };

            L.tileLayer(this.tileLayer, {
                attribution: this.attribution,
            }).addTo(this._mapInstance.map);

            this.initLocations();
        },

        initLocations() {
            const featureMarker = [];

            for (let location of this.currentLocations) {
                const markerOptions = {};
                if (location.entityId) {
                    markerOptions.entityId = location.entityId;
                }
                if (location.icon) {
                    markerOptions.icon = this.getIcon(location.icon);
                }

                const marker = L.marker(location.latlng, markerOptions);

                if (location.popup) {
                    const popupOptions = {
                        autoPan: false,
                        autoClose: true,
                    };
                    if (this.options) {
                        popupOptions.autoPan = this.options.includes('autoPan');
                        popupOptions.autoClose =
                            this.options.includes('autoClose');
                    }

                    marker
                        .bindPopup(location.popup, popupOptions)
                        .on('click', () => {
                            this.focusItem(location.entityId);
                        })
                        .on('popupclose', () => {
                            this.fitBounds();
                        });
                }

                featureMarker.push(marker);
            }

            if (this._mapInstance.layerGroup) {
                this._mapInstance.layerGroup.clearLayers();
            }

            this._mapInstance.layerGroup = L.featureGroup(featureMarker).addTo(
                this._mapInstance.map
            );

            this.fitBounds();
        },

        fitBounds() {
            this._mapInstance.map.fitBounds(
                this._mapInstance.layerGroup.getBounds(),
                {
                    padding: [5, 5],
                }
            );
        },

        focusItem(entityId) {
            this._mapInstance.layerGroup.eachLayer((layer) => {
                if (layer.options.entityId === entityId) {
                    if (!layer.getPopup().isOpen()) {
                        layer.openPopup();
                    }

                    this._mapInstance.map.flyTo(layer.getLatLng(), 16, {
                        animate: true,
                        duration: 1,
                    });
                }
            });
        },

        getIcon(icon) {
            if (icon.svg) {
                const size = 40;
                const iconOptions = {
                    iconSize: [size, size + size / 2],
                    iconAnchor: [size / 2, size + size / 2],
                    popupAnchor: [0, -size],
                    className: icon.className,
                    html: `<div class="marker-pin"></div>${icon.svg}`,
                };
                return L.divIcon(iconOptions);
            } else {
                return L.icon(icon);
            }
        },
    },
});
