import './config';

Shopware.Service('cmsService').registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'default-object-globe',
    name: 'moorl-address',
    label: 'sw-cms.elements.moorl-address.name',
    component: true,
    previewComponent: true,
    configComponent: 'sw-cms-el-config-moorl-address',
    defaultConfig: {
        zipcode: {
            source: 'static',
            value: null,
            required: true
        },
        city: {
            source: 'static',
            value: null,
            required: true
        },
        street: {
            source: 'static',
            value: null,
            required: true
        },
        streetNumber: {
            source: 'static',
            value: null,
            required: false
        },
        additionalAddressLine1: {
            source: 'static',
            value: null,
            required: false
        },
        additionalAddressLine2: {
            source: 'static',
            value: null,
            required: false
        },
        countryCode: {
            source: 'static',
            value: null,
            required: false
        },
        countryId: {
            source: 'static',
            value: null,
            required: false
        },
        countryStateId: {
            source: 'static',
            value: null,
            required: false
        },
        country: {
            source: 'static',
            value: null,
            required: false
        },
        countryState: {
            source: 'static',
            value: null,
            required: false
        },
        locationPlaceId: {
            source: 'static',
            value: null,
            required: false
        },
    }
});
