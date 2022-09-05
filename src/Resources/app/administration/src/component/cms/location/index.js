import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'default-object-globe',
    name: 'moorl-location',
    label: 'sw-cms.elements.moorl-location.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-location',
    defaultConfig: {
        locationLat: {
            source: 'static',
            value: 0,
            required: true
        },
        locationLon: {
            source: 'static',
            value: 0,
            required: true
        },
        markerId: {
            source: 'static',
            value: null,
            required: false
        },
        marker: {
            source: 'static',
            value: null,
            required: false
        },
        height: {
            source: 'static',
            value: '300px',
            required: false
        },
    }
});
