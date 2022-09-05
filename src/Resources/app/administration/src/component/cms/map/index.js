import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'default-object-globe',
    name: 'moorl-map',
    label: 'sw-cms.elements.moorl-map.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-map',
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
            required: true
        },
        height: {
            source: 'static',
            value: '300px',
            required: false
        },
    }
});
