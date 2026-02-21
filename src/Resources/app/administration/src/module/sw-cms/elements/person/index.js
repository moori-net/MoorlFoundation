import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-user',
    name: 'moorl-person',
    label: 'sw-cms.elements.moorl-person.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-person',
    defaultConfig: {
        salutationId: {
            source: 'static',
            value: null
        },
        firstName: {
            source: 'static',
            value: null,
        },
        lastName: {
            source: 'static',
            value: null
        },
        title: {
            source: 'static',
            value: null
        },
        company: {
            source: 'static',
            value: null
        },
    },
});
