import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'default-object-paperplane',
    name: 'moorl-contact',
    label: 'sw-cms.elements.moorl-contact.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-contact',
    defaultConfig: {
        email: {
            source: 'static',
            value: null,
            required: false
        },
        phoneNumber: {
            source: 'static',
            value: null,
            required: false
        },
        shopUrl: {
            source: 'static',
            value: null,
            required: false
        },
        merchantUrl: {
            source: 'static',
            value: null,
            required: false
        }
    }
});
