const Application = Shopware.Application;
import './component';
import './config';
import './preview';

Application.getContainer('service').cmsService.registerCmsElement({
    name: 'appflix-usp',
    label: 'sw-cms.elements.appflix-usp.title',
    component: 'sw-cms-el-appflix-usp',
    configComponent: 'sw-cms-el-config-appflix-usp',
    previewComponent: 'sw-cms-el-preview-appflix-usp',
    defaultConfig: {
        iconActive: {
            source: 'static',
            value: true,
        },
        iconClass: {
            source: 'static',
            value: 'fa fa-check',
        },
        title: {
            source: 'static',
            value: 'Lorem ipsum dolor'
        },
        text: {
            source: 'static',
            value: 'Lorem ipsum dolor sit amet, consetetur sadipscing'
        },
        iconColor: {
            source: 'static',
            value: '#4495c0'
        },
        headlineColor: {
            source: 'static',
            value: '#4c4c4c'
        },
        subHeadlineColor: {
            source: 'static',
            value: '#4c4c4c'
        },
        alignment: {
            source: 'static',
            value: 'left'
        },
    }
});
