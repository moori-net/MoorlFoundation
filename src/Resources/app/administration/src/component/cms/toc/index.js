import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'default-basic-stack-block',
    name: 'moorl-toc',
    label: 'sw-cms.elements.moorl-toc.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-toc',
    defaultConfig: {
        content: {
            source: 'static',
            value: null
        },
        offsetTop: {
            source: 'static',
            value: 20
        }
    }
});
