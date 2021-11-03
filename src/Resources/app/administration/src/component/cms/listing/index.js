import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'moorl-foundation-listing',
    label: 'moorl-foundation-listing',
    hidden: true,
    removable: false,
    component: 'sw-cms-el-moorl-foundation-listing',
    previewComponent: 'sw-cms-el-preview-moorl-foundation-listing',
    configComponent: 'sw-cms-el-config-moorl-foundation-listing',
    defaultConfig: {
        listingLayout: {
            source: 'static',
            value: 'grid'
        },
        itemLayout: {
            source: 'static',
            value: 'overlay'
        },
        displayMode: {
            source: 'static',
            value: 'cover'
        },
        limit: {
            source: 'static',
            value: 12
        },
        gapSize: {
            source: 'static',
            value: '20px'
        },
        itemWidth: {
            source: 'static',
            value: '320px'
        },
        itemHeight: {
            source: 'static',
            value: '240px'
        },
        contentPadding: {
            source: 'static',
            value: '20px'
        },
        contentBackgroundColor: {
            source: 'static',
            value: '#000'
        },
        contentColor: {
            source: 'static',
            value: '#FFF'
        },
        hasButton: {
            source: 'static',
            value: true
        },
        buttonClass: {
            source: 'static',
            value: 'btn btn-primary'
        },
        buttonLabel: {
            source: 'static',
            value: 'Click here!'
        }
    }
});