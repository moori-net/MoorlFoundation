const location = {
    locationLat: {
        cmsMappingField: true,
        type: 'number',
    },
    locationLon: {
        cmsMappingField: true,
        type: 'number',
    },
    markerId: {
        value: null,
        tab: 'general',
        card: 'general',
        cols: 12,
        entity: 'moorl_marker',
        cmsMappingField: true
    },
    height: {
        value: '300px',
        tab: 'general',
        card: 'general',
    },
    overrideOsmOptions: {
        value: false,
        tab: 'general',
        card: 'general',
        cols: 12,
    },
    osmOptions: {
        value: [],
        tab: 'general',
        card: 'general',
        cols: 12,
        componentName: 'moorl-select-field',
        attributes: {
            customSet: [
                'scrollWheelZoom',
                'dragging',
                'tap',
                'autoPan',
                'autoClose',
                'scrollTo',
                'fitBounds',
                'gestureHandling',
                'flyTo'
            ],
            snippetPath: 'moorl-foundation.field',
            multiple: true,
        },
        conditions: [{ property: 'overrideOsmOptions', value: true }],
    },
    legend: {
        value: false,
        tab: 'general',
        card: 'general',
        cols: 12,
    },
    legendItems: {
        value: [],
        tab: 'general',
        card: 'general',
        cols: 12,
        componentName: 'sw-entity-multi-id-select',
        entity: 'moorl_marker',
        conditions: [{ property: 'legend', value: true }],
    },
};

export default location;