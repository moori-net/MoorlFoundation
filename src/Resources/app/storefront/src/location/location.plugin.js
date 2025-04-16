import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';
import L from 'leaflet';
import { GestureHandling } from 'leaflet-gesture-handling';
import CookieStorageHelper from 'src/helper/storage/cookie-storage.helper';

export default class MoorlLocationPlugin extends Plugin {
    static options = {
        locations: [],
        mapSelector: '.moorl-location-map',
        legendSelector: '.legend',
        tileLayer: '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution:
            'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        options: [],
        offsetTop: 120,
        padding: 5,
        zoom: 14,
        cookieConsent: false,
    };

    init() {
        this.cookieEnabledName = 'moorl-location-map';

        this._mapElement = this.el.querySelector(this.options.mapSelector);
        this._legendElement = this.el.querySelector(
            this.options.legendSelector
        );

        this._initMap();
        this._initLocations(this.options.locations);

        this._registerEvents();
    }

    _registerEvents() {
        /*document.$emitter.subscribe(COOKIE_CONFIGURATION_UPDATE, (updatedCookies) => {});*/

        const listingEl = DomAccess.querySelector(
            document,
            '.cms-element-product-listing-wrapper',
            false
        );

        if (listingEl) {
            const listingPlugin =
                window.PluginManager.getPluginInstanceFromElement(
                    listingEl,
                    'Listing'
                );
            if (!listingPlugin) {
                return;
            }

            listingPlugin.$emitter.subscribe(
                'Listing/afterRenderResponse',
                () => {
                    this._initLocationsFromListing();
                }
            );

            this._initLocationsFromListing();
        }
    }

    _initMap() {
        if (
            this.options.cookieConsent &&
            !CookieStorageHelper.getItem(this.cookieEnabledName)
        ) {
            return;
        }

        if (!this._mapElement) {
            return;
        }

        const mapOptions = {
            scrollWheelZoom: true,
            dragging: true,
            tap: true,
        };
        if (this.options.options) {
            mapOptions.scrollWheelZoom =
                this.options.options.includes('scrollWheelZoom');
            mapOptions.dragging = this.options.options.includes('dragging');
            mapOptions.tap = this.options.options.includes('tap');
            mapOptions.gestureHandling =
                this.options.options.includes('gestureHandling');
        }

        L.Map.addInitHook('addHandler', 'gestureHandling', GestureHandling);

        this._mapInstance = {};
        this._mapInstance.layerGroup = L.layerGroup([]);
        this._mapInstance.map = L.map(this._mapElement, mapOptions);

        L.tileLayer(this.options.tileLayer, {
            attribution: this.options.attribution,
        }).addTo(this._mapInstance.map);

        if (this._legendElement) {
            const legend = L.control({ position: 'bottomleft' });
            legend.onAdd = (map) => {
                return this._legendElement.cloneNode(true);
            };
            legend.addTo(this._mapInstance.map);
            this._legendElement.remove();
        }
    }

    _initLocationsFromListing() {
        const listingElements = document.querySelectorAll(
            'ul.js-listing-wrapper > li'
        );
        const locations = [];

        if (listingElements) {
            listingElements.forEach((listingElement) => {
                locations.push(
                    JSON.parse(listingElement.dataset.entityLocation)
                );
                listingElement.addEventListener('click', () => {
                    this._focusItem(listingElement.dataset.entityId);
                });
            });
        }

        this._initLocations(locations);
    }

    _initLocations(locations) {
        const featureMarker = [];

        for (let location of locations) {
            if (location.radius) {
                const circle = L.circle(location.latlng, location.radius);

                featureMarker.push(circle);
                continue;
            }

            const markerOptions = {};
            if (location.entityId) {
                markerOptions.entityId = location.entityId;
            }
            if (location.icon) {
                markerOptions.icon = this._getIcon(location.icon);
            }

            const marker = L.marker(location.latlng, markerOptions);

            if (location.popup) {
                const popupOptions = {
                    autoPan: false,
                    autoClose: true,
                };
                if (this.options.options) {
                    popupOptions.autoPan =
                        this.options.options.includes('autoPan');
                    popupOptions.autoClose =
                        this.options.options.includes('autoClose');
                }

                marker
                    .bindPopup(location.popup, popupOptions)
                    .on('click', () => {
                        this._focusItem(location.entityId);
                    })
                    .on('popupclose', () => {
                        if (this.options.options) {
                            if (this.options.options.includes('fitBounds')) {
                                this._fitBounds();
                            }
                        }
                    });
            }

            featureMarker.push(marker);
        }

        if (!this._mapInstance) {
            return;
        }

        if (this._mapInstance.layerGroup) {
            this._mapInstance.layerGroup.clearLayers();
        }

        this._mapInstance.layerGroup = L.featureGroup(featureMarker).addTo(
            this._mapInstance.map
        );

        this._fitBounds();
    }

    _fitBounds() {
        this._mapInstance.map.fitBounds(
            this._mapInstance.layerGroup.getBounds(),
            {
                padding: [this.options.padding, this.options.padding],
            }
        );

        this._updateListingElements(null);
    }

    _focusItem(entityId) {
        this._mapInstance.layerGroup.eachLayer((layer) => {
            if (layer.options.entityId === entityId) {
                if (!layer.getPopup().isOpen()) {
                    layer.openPopup();
                }
                if (this.options.options) {
                    if (this.options.options.includes('flyTo')) {
                        this._mapInstance.map.flyTo(
                            layer.getLatLng(),
                            this.options.zoom,
                            { animate: true, duration: 1 }
                        );
                    }
                }
            }
        });

        this._updateListingElements(entityId);
    }

    _updateListingElements(entityId) {
        const listingElements = document.querySelectorAll(
            'ul.js-listing-wrapper > li'
        );
        if (listingElements) {
            listingElements.forEach((listingElement) => {
                listingElement.classList.remove('is-active');
                listingElement.classList.remove('shadow');

                if (listingElement.dataset.entityId === entityId) {
                    listingElement.classList.add('is-active');
                    listingElement.classList.add('shadow');

                    if (this.options.options) {
                        if (this.options.options.includes('scrollTo')) {
                            let topPos =
                                listingElement.getBoundingClientRect().top +
                                window.scrollY -
                                this.options.offsetTop;

                            window.scrollTo({
                                top: topPos,
                                behavior: 'smooth',
                            });
                        }
                    }
                }
            });
        }
    }

    _getIcon(icon) {
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
    }
}
