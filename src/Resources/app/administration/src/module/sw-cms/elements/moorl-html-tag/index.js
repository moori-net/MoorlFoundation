const Application = Shopware.Application;
import './component';
import './config';
import './preview';

Application.getContainer('service').cmsService.registerCmsElement({
    name: 'moorl-html-tag',
    label: 'sw-cms.elements.moorl-html-tag.title',
    component: 'sw-cms-el-moorl-html-tag',
    configComponent: 'sw-cms-el-config-moorl-html-tag',
    previewComponent: 'sw-cms-el-preview-moorl-html-tag',
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
