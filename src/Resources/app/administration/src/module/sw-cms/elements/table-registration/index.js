import './component';
import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'AppflixDewaShop',
    icon: 'regular-clock',
    name: 'moorl-table-registration',
    label: 'sw-cms.elements.moorl-table-registration.title',
    component: 'sw-cms-el-moorl-table-registration',
    configComponent: 'sw-cms-el-config-moorl-table-registration',
    previewComponent: true,
    defaultConfig: {
        type: {
            source: 'static',
            value: 'contact',
        },
        content: {
            source: 'static',
            value: '',
        },
        privacyActive: {
            source: 'static',
            value: true,
        },
        verticalAlign: {
            source: 'static',
            value: null,
        },
    },
});
