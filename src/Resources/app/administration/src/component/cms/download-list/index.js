import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    name: 'moorl-download-list',
    label: 'sw-cms.elements.moorl-download-list.name',
    component: 'sw-cms-el-moorl-download-list',
    previewComponent: 'sw-cms-el-preview-moorl-download-list',
    configComponent: 'sw-cms-el-config-moorl-download-list',
    defaultConfig: {
        downloads: {
            source: 'static',
            value: [],
            required: true,
            entity: {
                name: 'media'
            }
        },
        layout: {
            source: 'static',
            value: 'default'
        }
    }
});
