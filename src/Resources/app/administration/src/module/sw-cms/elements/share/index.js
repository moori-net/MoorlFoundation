import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-share',
    name: 'moorl-share',
    label: 'sw-cms.elements.moorl-share.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-share',
    defaultConfig: {
        provider: {
            source: 'static',
            value: ['facebook', 'twitter']
        },
    },
});
