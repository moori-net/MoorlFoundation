import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-home',
    name: 'moorl-address',
    label: 'sw-cms.elements.moorl-address.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-address',
    defaultConfig: {
        zipcode: {
            source: 'static',
            value: null
        },
        city: {
            source: 'static',
            value: null
        },
        street: {
            source: 'static',
            value: null
        },
        streetNumber: {
            source: 'static',
            value: null
        },
        additionalAddressLine1: {
            source: 'static',
            value: null
        },
        additionalAddressLine2: {
            source: 'static',
            value: null
        },
        countryCode: {
            source: 'static',
            value: null
        },
        countryId: {
            source: 'static',
            value: null
        },
        countryStateId: {
            source: 'static',
            value: null
        },
        country: {
            source: 'static',
            value: null
        },
        countryState: {
            source: 'static',
            value: null
        },
        locationPlaceId: {
            source: 'static',
            value: null
        },
    },
});
