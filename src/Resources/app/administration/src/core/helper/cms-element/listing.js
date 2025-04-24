const listing = {
    listingSource: {
        componentName: 'moorl-select-field',
        attributes: {
            set: 'listingSource'
        },
        value: 'static',
        tab: 'general',
        card: 'source',
        cols: 6
    },
    listingSorting: {
        entity: 'moorl_sorting',
        attributes: {
            labelProperty: 'label'
        },
        tab: 'general',
        card: 'source',
        cols: 6
    },
    foreignKey: {
        value: null,
        tab: 'general',
        card: 'source',
        cols: 6
    },
    limit: {
        value: 12,
        tab: 'general',
        card: 'source',
        cols: 6,
        attributes: { max: 24 }
    },
    listingItemIds: {
        value: [],
        tab: 'general',
        card: 'source',
        cols: 12,
        conditions: [{ property: 'listingSource', value: 'select' }],
        componentName: 'sw-entity-multi-id-select',
        attributes: {
            entity: ({entity}) => entity
        },
    },
    // Layout
    listingLayout: {
        value: 'grid',
        tab: 'layout',
        card: 'general',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'listingLayout' }
    },
    listingJustifyContent: {
        value: 'normal',
        tab: 'layout',
        card: 'general',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'cssJustifyContent' }
    },
    itemLayout: {
        value: 'image-content',
        tab: 'layout',
        card: 'general',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'itemLayout' }
    },
    displayMode: {
        value: 'cover',
        tab: 'layout',
        card: 'general',
        componentName: 'moorl-select-field',
        attributes: { set: 'displayMode' },
        cols: 6,
        conditions: [{ property: 'itemLayout', value: 'custom', operator: '!='}]
    },
    itemLayoutTemplate: {
        tab: 'layout',
        card: 'general',
        value: '@Storefront/storefront/component/product/card/box.html.twig',
        conditions: [{ property: 'itemLayout', value: 'custom' }]
    },

    listingHeaderTitle: {
        value: null,
        tab: 'layout',
        card: 'header',
        cols: 12
    },
    gapSize: {
        value: '20px',
        tab: 'layout',
        card: 'main',
        cols: 4
    },
    itemWidth: {
        value: '300px',
        tab: 'layout',
        card: 'item',
        cols: 4
    },
    itemHeight: {
        value: '400px',
        tab: 'layout',
        card: 'item',
        cols: 4
    },
    itemPadding: {
        value: '0px',
        tab: 'layout',
        card: 'item',
        cols: 4
    },
    hasButton: {
        value: true,
        tab: 'content',
        card: 'button',
        cols: 6
    },
    buttonLabel: {
        value: 'Click here!',
        tab: 'content',
        card: 'button',
        cols: 6,
        conditions: [{ property: 'hasButton', value: true }]
    },
    buttonClass: {
        value: 'btn btn-primary',
        tab: 'content',
        card: 'button',
        cols: 6,
        conditions: [{ property: 'hasButton', value: true }]
    },
    urlNewTab: {
        value: true,
        tab: 'content',
        card: 'button',
        cols: 6,
        conditions: [{ property: 'hasButton', value: true }]
    },
    textAlign: {
        value: 'left',
        tab: 'content',
        card: 'text',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'textHorizontalAlign' }
    },
    contentLength: {
        value: 150,
        tab: 'content',
        card: 'text',
        cols: 6
    },
    contentPadding: {
        value: '20px',
        tab: 'layout',
        card: 'content',
        cols: 4
    },
    contentColor: {
        type: 'color',
        value: null,
        tab: 'layout',
        card: 'content',
        cols: 4
    },
    contentBackgroundColor: {
        type: 'color',
        value: null,
        tab: 'layout',
        card: 'content',
        cols: 4
    },
    contentHighlightColor: {
        type: 'color',
        value: null,
        tab: 'layout',
        card: 'content',
        cols: 4
    },
    itemBackgroundColor: {
        type: 'color',
        value: null,
        tab: 'layout',
        card: 'item',
        cols: 6
    },
    itemHasBorder: {
        value: false,
        tab: 'layout',
        card: 'item',
        cols: 6
    },
    // Slider - Slider
    mode: {
        tab: 'slider',
        card: 'slider',
        value: 'carousel',
        componentName: 'moorl-select-field',
        attributes: { set: 'sliderMode' },
        conditions: [{ property: 'listingLayout', value: 'slider' }]
    },
    speed: {
        tab: 'slider',
        card: 'slider',
        value: 1000,
        cols: 6,
        conditions: [{ property: 'listingLayout', value: 'slider' }]
    },
    autoplayTimeout: {
        tab: 'slider',
        card: 'slider',
        value: 3000,
        cols: 6,
        conditions: [{ property: 'listingLayout', value: 'slider' }]
    },
    autoplay: {
        tab: 'slider',
        card: 'slider',
        value: true,
        cols: 6,
        conditions: [{ property: 'listingLayout', value: 'slider' }]
    },
    autoplayHoverPause: {
        tab: 'slider',
        card: 'slider',
        value: true,
        cols: 6,
        conditions: [
            { property: 'autoplay', value: true },
            { property: 'listingLayout', value: 'slider' }
        ]
    },
    mouseDrag: {
        tab: 'slider',
        card: 'slider',
        value: false,
        cols: 6,
        conditions: [{ property: 'listingLayout', value: 'slider' },]
    },
    // Slider - Animation
    animateIn: {
        value: null,
        tab: 'slider',
        card: 'animation',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'animateIn' },
        conditions: [
            { property: 'mode', value: 'gallery' },
            { property: 'listingLayout', value: 'slider' }
        ]
    },
    animateOut: {
        value: null,
        tab: 'slider',
        card: 'animation',
        cols: 6,
        componentName: 'moorl-select-field',
        conditions: [
            { property: 'mode', value: 'gallery' },
            { property: 'listingLayout', value: 'slider' }
        ]
    },
    // Slider - Navigation
    navigationArrows: {
        tab: 'slider',
        card: 'navigation',
        value: 'outside',
        componentName: 'moorl-select-field',
        attributes: { set: 'navigationArrows' },
        cols: 6,
        conditions: [{ property: 'listingLayout', value: 'slider' }]
    },
    navigationDots: {
        tab: 'slider',
        card: 'navigation',
        value: null,
        componentName: 'moorl-select-field',
        attributes: { set: 'navigationDots' },
        cols: 6,
        conditions: [{ property: 'listingLayout', value: 'slider' }]
    }
};

export default listing;
