const Application = Shopware.Application;
import './component';
import './config';

Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-checkmark',
    name: 'moorl-usp',
    label: 'sw-cms.elements.moorl-usp.title',
    component: 'sw-cms-el-moorl-usp',
    configComponent: 'sw-cms-el-config-moorl-usp',
    previewComponent: true,
    defaultConfig: {
        iconActive: {
            source: 'static',
            value: true,
        },
        iconClass: {
            source: 'static',
            value: 'fab|shopware',
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
