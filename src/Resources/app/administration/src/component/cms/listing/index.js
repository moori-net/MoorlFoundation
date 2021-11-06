import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    hidden: true,
    name: 'moorl-foundation-listing',
    label: 'sw-cms.elements.moorl-foundation-listing.name',
    component: 'sw-cms-el-moorl-foundation-listing',
    previewComponent: 'sw-cms-el-preview-moorl-foundation-listing',
    configComponent: 'sw-cms-el-config-moorl-foundation-listing',
    defaultConfig: {
        listingSource: {
            source: 'static',
            value: 'static'
        },
        listingSorting: {
            source: 'static',
            value: null
        },
        listingItemIds: {
            source: 'static',
            value: []
        },
        listingLayout: {
            source: 'static',
            value: 'grid'
        },
        itemLayout: {
            source: 'static',
            value: 'overlay'
        },
        itemLayoutTemplate: {
            source: 'static',
            value: '@Storefront/storefront/component/product/card/box.html.twig'
        },
        displayMode: {
            source: 'static',
            value: 'cover'
        },
        textAlign: {
            source: 'static',
            value: 'left'
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
            value: '300px'
        },
        itemHeight: {
            source: 'static',
            value: '400px'
        },
        itemPadding: {
            source: 'static',
            value: '0px'
        },
        itemBackgroundColor: {
            source: 'static',
            value: '#f9f9f9'
        },
        itemHasBorder: {
            source: 'static',
            value: false
        },
        contentPadding: {
            source: 'static',
            value: '20px'
        },
        contentBackgroundColor: {
            source: 'static',
            value: '#f9f9f9'
        },
        contentColor: {
            source: 'static',
            value: null
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
        },
        speed: {
            source: 'static',
            value: 2000
        },
        autoplayTimeout: {
            source: 'static',
            value: 6000
        },
        autoplay: {
            source: 'static',
            value: true
        },
        autoplayHoverPause: {
            source: 'static',
            value: true
        },
        animateIn: {
            source: 'static',
            value: null
        },
        animateOut: {
            source: 'static',
            value: null
        },
        navigation: {
            source: 'static',
            value: true
        }
    }
});
