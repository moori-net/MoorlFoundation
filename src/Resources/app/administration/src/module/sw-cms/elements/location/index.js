import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-map',
    name: 'moorl-location',
    label: 'sw-cms.elements.moorl-location.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-location',
    defaultConfig: {
        locationLat: {
            source: 'static',
            value: 0
        },
        locationLon: {
            source: 'static',
            value: 0
        },
        markerId: {
            source: 'static',
            value: null
        },
        marker: {
            source: 'static',
            value: null
        },
        height: {
            source: 'static',
            value: '300px'
        },
        overrideOsmOptions: {
            source: 'static',
            value: false
        },
        osmOptions: {
            source: 'static',
            value: []
        },
        legend: {
            source: 'static',
            value: false
        },
        legendItems: {
            source: 'static',
            value: []
        },
    },
});
