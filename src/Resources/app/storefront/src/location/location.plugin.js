import Plugin from 'src/plugin-system/plugin.class';
import L from 'leaflet';

export default class MoorlLocationPlugin extends Plugin {
    static options = {
        locations: [],
        mapSelector: '.moorl-location-map',
        tileLayer: '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        options: []
    };

    init() {
        this._mapElement = this.el.querySelector(this.options.mapSelector);

        this._initMap();
        this._initLocations(this.options.locations);
    }

    _initMap() {
        if (!this._mapElement) {
            return;
        }

        const mapOptions = {};
        if (this.options.options) {
            mapOptions.scrollWheelZoom = this.options.options.includes('scrollWheelZoom');
            mapOptions.dragging = this.options.options.includes('dragging');
            mapOptions.tap = this.options.options.includes('tap');
        }

        this._mapInstance = {};
        this._mapInstance.layerGroup = L.layerGroup([]);
        this._mapInstance.map = L.map(this._mapElement, mapOptions);

        L.tileLayer(this.options.tileLayer, {
            attribution: this.options.attribution
        }).addTo(this._mapInstance.map);
    }

    _initLocations(locations) {
        const featureMarker = [];

        for (let location of locations) {
            const markerOptions = {};

            if (location.entityId) {
                markerOptions.entityId = location.entityId;
            }

            if (location.icon) {
                markerOptions.icon = L.icon(location.icon);
            }

            const marker = L.marker(location.latlng, markerOptions);

            if (location.popup) {
                marker
                    .bindPopup(location.popup, {autoPan: false, autoClose: true})
                    .on('click', () => {
                        this._focusItem(location.entityId)
                    })
                    .on('popupclose', () => {
                        this._fitBounds();
                    });
            }

            featureMarker.push(marker);
        }

        if (this._mapInstance.layerGroup) {
            this._mapInstance.layerGroup.clearLayers();
        }
        this._mapInstance.layerGroup = L.featureGroup(featureMarker).addTo(this._mapInstance.map);

        this._fitBounds();
    }

    _fitBounds() {
        this._mapInstance.map.fitBounds(this._mapInstance.layerGroup.getBounds(), {
            padding: [5, 5]
        });

        this._updateListingElements(null);
    }

    _focusItem(entityId) {
        this._mapInstance.layerGroup.eachLayer((layer) => {
            if (layer.options.entityId === entityId) {
                if (!layer.getPopup().isOpen()) {
                    layer.openPopup();
                }

                this._mapInstance.map.flyTo(layer.getLatLng(), 16, {animate: true, duration: 1});
            }
        });

        this._updateListingElements(entityId);
    }

    _updateListingElements(entityId) {
        const listingElements = document.querySelectorAll('ul.js-listing-wrapper > li');
        if (listingElements) {
            listingElements.forEach((listingElement) => {
                listingElement.classList.remove('is-active');
                if (listingElement.dataset.entityId === entityId) {
                    listingElement.classList.add('is-active');

                    window.scrollTo({
                        top: listingElement.offsetTop,
                        behavior: 'smooth',
                    });
                }
            });
        }
    }
}
