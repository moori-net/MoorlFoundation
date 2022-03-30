import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'moorl-share',
    label: 'sw-cms.elements.moorl-share.name',
    component: 'sw-cms-el-moorl-share',
    previewComponent: 'sw-cms-el-preview-moorl-share',
    configComponent: 'sw-cms-el-config-moorl-share',
    defaultConfig: {
        provider: {
            source: 'static',
            value: ['facebook','twitter'],
            required: true
        }
    }
});
