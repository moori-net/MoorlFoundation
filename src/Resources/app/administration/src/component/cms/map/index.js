import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'default-web-link',
    name: 'moorl-map',
    label: 'sw-cms.elements.moorl-map.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-map',
    defaultConfig: {
        provider: {
            source: 'static',
            value: ['facebook','twitter'],
            required: true
        }
    }
});
