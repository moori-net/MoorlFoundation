const Application = Shopware.Application;
import './component';
import './config';

Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'default-text-code',
    name: 'moorl-html-tag',
    label: 'sw-cms.elements.moorl-html-tag.title',
    component: 'sw-cms-el-moorl-html-tag',
    configComponent: 'sw-cms-el-config-moorl-html-tag',
    previewComponent: true,
    defaultConfig: {
        tag: {
            source: 'static',
            value: 'h1',
        },
        content: {
            source: 'static',
            value: 'moori',
        },
        cssClass: {
            source: 'static',
            value: 'h1 text-center',
        },
        style: {
            source: 'static',
            value: 'color:#333;'
        },
    }
});
