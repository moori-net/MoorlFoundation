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
            value: null,
            required: true,
        },
        firstName: {
            source: 'static',
            value: null,
            required: true,
        },
        lastName: {
            source: 'static',
            value: null,
            required: true,
        },
        title: {
            source: 'static',
            value: null,
            required: false,
        },
        company: {
            source: 'static',
            value: null,
            required: false,
        },
    },
});
