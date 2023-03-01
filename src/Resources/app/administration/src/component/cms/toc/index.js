import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-bars-square',
    name: 'moorl-toc',
    label: 'sw-cms.elements.moorl-toc.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-toc',
    defaultConfig: {
        content: {
            source: 'static',
            value: null
        }
    }
});
