import './component';
import './config';

Shopware.Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-bars-square',
    name: 'moorl-accordion',
    label: 'sw-cms.elements.moorl-accordion.title',
    component: 'sw-cms-el-moorl-accordion',
    configComponent: 'sw-cms-el-config-moorl-accordion',
    previewComponent: true,
    defaultConfig: {
        name: {
            source: 'static',
            value: 'My Accordion',
        },
        entries: {
            source: 'static',
            value: [
                {
                    id: 1,
                    name: 'This is my first entry',
                    content:
                        '<p>Lorem ipsum dolor sit amet, <a href="#">consetetur</a> sadipscing elitr, <b>sed diam nonumy eirmod tempor</b> invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua</p>',
                }
            ],
        },
        autoClose: {
            source: 'static',
            value: true,
        },
        verticalAlign: {
            source: 'static',
            value: null,
        },
    },
});
