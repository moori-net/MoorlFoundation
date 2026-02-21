import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-paper-plane',
    name: 'moorl-contact',
    label: 'sw-cms.elements.moorl-contact.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-contact',
    defaultConfig: {
        email: {
            source: 'static',
            value: null
        },
        phoneNumber: {
            source: 'static',
            value: null
        },
        shopUrl: {
            source: 'static',
            value: null
        },
        merchantUrl: {
            source: 'static',
            value: null
        },
    },
});
