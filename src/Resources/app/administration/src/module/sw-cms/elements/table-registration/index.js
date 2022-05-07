import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    hidden: true,
    name: 'appflix-table-registration',
    label: 'sw-cms.elements.appflix-table-registration.title',
    component: 'sw-cms-el-appflix-table-registration',
    configComponent: 'sw-cms-el-config-appflix-table-registration',
    previewComponent: 'sw-cms-el-preview-appflix-table-registration',
    defaultConfig: {
        type: {
            source: 'static',
            value: 'contact'
        },
        content: {
            source: 'static',
            value: ''
        },
        privacyActive: {
            source: 'static',
            value: true
        },
        verticalAlign: {
            source: 'static',
            value: null
        }
    }
});
