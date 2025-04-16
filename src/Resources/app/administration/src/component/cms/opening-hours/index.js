import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-clock',
    name: 'moorl-opening-hours',
    label: 'sw-cms.elements.moorl-opening-hours.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-opening-hours',
    defaultConfig: {
        openingHours: {
            source: 'static',
            value: null,
            required: true,
        },
        timeZone: {
            source: 'static',
            value: 'Europe/Berlin',
            required: true,
        },
    },
});
