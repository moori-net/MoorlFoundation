import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import queryString from 'query-string';
import L from 'leaflet';

export default class MoorlLocationPlugin extends Plugin {
    static options = {
        locations: [],
        mapSelector: '.moorl-location-map',
        tileLayer: '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
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

        this._mapInstance = {};
        this._mapInstance.layerGroup = L.layerGroup([]);
        this._mapInstance.map = L.map(this._mapElement, {
            scrollWheelZoom: false
        });

        L.tileLayer(this.options.tileLayer, {
            attribution: this.options.attribution
        }).addTo(this._mapInstance.map);
    }

    _initLocations(locations) {
        const featureMarker = [];

        for (let location of locations) {
            featureMarker.push(L.marker(location.latlng, location.options).bindPopup(location.popup, {autoPan: false, autoClose: true}));
        }

        if (this._mapInstance.layerGroup) {
            this._mapInstance.layerGroup.clearLayers();
        }
        this._mapInstance.layerGroup = L.featureGroup(featureMarker).addTo(this._mapInstance.map);

        this._mapInstance.map.fitBounds(this._mapInstance.layerGroup.getBounds(), {
            padding: [5, 5]
        });
    }
}
