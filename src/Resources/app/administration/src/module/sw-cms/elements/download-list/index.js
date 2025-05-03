import './component';
import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-cloud-download',
    name: 'moorl-download-list',
    label: 'sw-cms.elements.moorl-download-list.name',
    component: 'sw-cms-el-moorl-download-list',
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-download-list',
    defaultConfig: {
        downloads: {
            source: 'static',
            value: [],
            required: true,
            entity: {
                name: 'media',
            },
        },
        layout: {
            source: 'static',
            value: 'default',
        },
        emptyText: {
            source: 'static',
            value: 'No downloads available',
        },
    },
});
