const ctaBanner = {
    elementType: {
        value: 'custom',
        tab: 'general',
        card: 'general',
        cols: 12,
        componentName: 'moorl-select-field',
        attributes: {
            customSet: ['custom', 'category', 'cta', 'product'],
            snippetPath: 'moorl-foundation.field'
        }
    },
    height: {
        value: '300px',
        tab: 'general',
        card: 'general',
        cols: 6,
    },
    elementBackground: {
        value: null,
        tab: 'general',
        card: 'general',
        cols: 6,
        type: 'color',
        attributes: { colorOutput: 'hex', zIndex: 1000 }
    },
    overlayBackground: {
        value: null,
        tab: 'general',
        card: 'general',
        cols: 6,
        type: 'color',
        attributes: { colorOutput: 'hex', zIndex: 1000 }
    },
    elementClickable: {
        value: false,
        tab: 'general',
        card: 'general',
        cols: 6,
    },
    category: {
        value: null,
        tab: 'general',
        card: 'general',
        cols: 12,
        entity: 'category',
        conditions: [{ property: 'elementType', value: 'category' }]
    },
    product: {
        value: null,
        tab: 'general',
        card: 'general',
        cols: 12,
        entity: 'product',
        conditions: [{ property: 'elementType', value: 'product' }]
    },
    contentLength: {
        value: 300,
        tab: 'general',
        card: 'general',
        cols: 12,
        conditions: [
            { property: 'elementType', value: 'category' },
            { property: 'elementType', value: 'product' }
        ]
    },
    elementUrl: {
        value: null,
        tab: 'general',
        card: 'general',
        cols: 6,
        conditions: [{ property: 'elementClickable', value: true }]
    },
    elementNewTab: {
        value: false,
        tab: 'general',
        card: 'general',
        cols: 6,
        conditions: [{ property: 'elementClickable', value: true }]
    },
    mediaActive: {
        value: true,
        tab: 'media',
        card: 'media',
        cols: 6,
        
    },
    mediaHover: {
        value: 'zoom',
        tab: 'media',
        card: 'media',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: {
            customSet: [
                'zoom', 'rotate', 'rotate-zoom',
                'colorize', 'colorize-zoom',
                'colorize-blur', 'blur', 'blur-zoom'
            ]
        }
    },
    backgroundFixed: {
        value: false,
        tab: 'media',
        card: 'media',
        cols: 6,
        
    },
    backgroundDisplayMode: {
        value: 'cover',
        tab: 'media',
        card: 'media',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'displayMode' }
    },
    backgroundSizeX: {
        value: '300px',
        tab: 'media',
        card: 'media',
        cols: 6,
        conditions: [{ property: 'backgroundDisplayMode', value: 'custom' }]
    },
    backgroundSizeY: {
        value: '300px',
        tab: 'media',
        card: 'media',
        cols: 6,
        conditions: [{ property: 'backgroundDisplayMode', value: 'custom' }]
    },
    backgroundVerticalAlign: {
        value: 'center',
        tab: 'media',
        card: 'media',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'textVerticalAlign' }
    },
    backgroundHorizontalAlign: {
        value: 'center',
        tab: 'media',
        card: 'media',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'textHorizontalAlign' }
    },
    media: {
        value: null,
        tab: 'media',
        card: 'media',
        cols: 12,
        entity: 'media',
        conditions: [{ property: 'mediaActive', value: true }]
    },
    videoActive: {
        value: false,
        tab: 'media',
        card: 'video',
        cols: 6,
        conditions: [{ property: 'mediaActive', value: true }]
    },
    videoAutoplay: {
        value: true,
        tab: 'media',
        card: 'video',
        cols: 6,
        conditions: [{ property: 'videoActive', value: true }]
    },
    videoLoop: {
        value: true,
        tab: 'media',
        card: 'video',
        cols: 6,
        conditions: [{ property: 'videoActive', value: true }]
    },
    videoDisplayMode: {
        value: 'cover',
        tab: 'media',
        card: 'video',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'displayMode' },
        conditions: [{ property: 'videoActive', value: true }]
    },
    titleTag: {
        value: 'h3',
        tab: 'content',
        card: 'text',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: {customSet: ['h1','h2','h3','h4','h5','div']},
        conditions: [{ property: 'elementType', value: 'custom', operator: '!=' }]
    },
    title: {
        value: 'Lorem ipsum dolor',
        tab: 'content',
        card: 'text',
        cols: 6,
        conditions: [{ property: 'elementType', value: 'custom', operator: '!=' }]
    },
    quote: {
        value: '',
        tab: 'content',
        card: 'text',
        cols: 12,
        conditions: [{ property: 'elementType', value: 'custom', operator: '!=' }]
    },
    content: {
        type: 'html',
        value: '',
        tab: 'content',
        card: 'text',
        cols: 12,
        conditions: [{ property: 'elementType', value: 'custom' }]
    },
    btnActive: {
        value: true,
        tab: 'content',
        card: 'button',
        cols: 6,
        conditions: [{ property: 'elementType', value: 'custom', operator: '!=' }]
    },
    btnText: {
        value: 'Shop now',
        tab: 'content',
        card: 'button',
        cols: 6,
        conditions: [{ property: 'btnActive', value: true }]
    },
    btnClass: {
        value: 'primary',
        tab: 'content',
        card: 'button',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'bsButton' },
        conditions: [{ property: 'btnActive', value: true }]
    },
    btnUrl: {
        value: '',
        tab: 'content',
        card: 'button',
        cols: 6,
        conditions: [{ property: 'btnActive', value: true }]
    },
    btnNewTab: {
        value: false,
        tab: 'content',
        card: 'button',
        cols: 6,
        conditions: [{ property: 'btnActive', value: true }]
    },
    // Icon
    iconType: {
        value: 'none',
        tab: 'content',
        card: 'icon',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: {
            set: 'iconType',
            showClearableButton: true
        }
    },
    iconClass: {
        value: '',
        tab: 'content',
        card: 'icon',
        cols: 6,
        attributes: {placeholder: 'fab|shopware or fa6s|check'},
        conditions: [{ property: 'iconType', value: 'fa' }]
    },
    iconSvg: {
        value: '',
        tab: 'content',
        card: 'icon',
        cols: 12,
        type: 'code',
        attributes: { softWraps: false },
        conditions: [{ property: 'iconType', value: 'svg' }]
    },
    iconMedia: {
        value: null,
        tab: 'content',
        card: 'icon',
        cols: 12,
        entity: 'media',
        conditions: [{ property: 'iconType', value: 'media' }]
    },
    iconPosition: {
        value: 'left',
        tab: 'content',
        card: 'icon',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { customSet: ['top','left'] },
        conditions: [{ property: 'iconType', value: 'none', operator: '!=' }]
    },
    iconFontSize: {
        value: '30px',
        tab: 'content',
        card: 'icon',
        cols: 6,
        conditions: [{ property: 'iconType', value: 'none', operator: '!=' }]
    },
    iconMarginRight: {
        value: '15px',
        tab: 'content',
        card: 'icon',
        cols: 6,
        conditions: [{ property: 'iconType', value: 'none', operator: '!=' }]
    },
    iconMarginBottom: {
        value: '15px',
        tab: 'content',
        card: 'icon',
        cols: 6,
        conditions: [{ property: 'iconType', value: 'none', operator: '!=' }]
    },
    // Box
    boxVerticalAlign: {
        value: 'center',
        tab: 'position',
        card: 'box',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'flexVerticalAlign' }
    },
    boxHorizontalAlign: {
        value: 'center',
        tab: 'position',
        card: 'box',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'flexHorizontalAlign' }
    },
    boxTextAlign: {
        value: 'left',
        tab: 'position',
        card: 'box',
        cols: 6,
        componentName: 'moorl-select-field',
        attributes: { set: 'textHorizontalAlign' }
    },
    boxWidth: {
        value: 'auto',
        tab: 'position',
        card: 'box',
        cols: 6,
    },
    boxHeight: {
        value: 'auto',
        tab: 'position',
        card: 'box',
        cols: 6,
    },
    boxMargin: {
        value: '20px',
        tab: 'position',
        card: 'box',
        cols: 6,
    },
    boxPadding: {
        value: '15px',
        tab: 'position',
        card: 'box',
        cols: 6,
    },
    boxColor: {
        value: '#000000',
        tab: 'position',
        card: 'box-style',
        cols: 6,
        type: 'color',
        attributes: { colorOutput: 'hex', zIndex: 1000 }
    },
    boxBackground: {
        value: 'rgba(255,255,255,0.7)',
        tab: 'position',
        card: 'box-style',
        cols: 6,
        type: 'color',
        attributes: { colorOutput: 'hex', zIndex: 1000 }
    },
    boxBorderRadius: {
        value: '0px',
        tab: 'position',
        card: 'box-style',
        cols: 6,
    },
    boxMaxWidth: {
        value: false,
        tab: 'position',
        card: 'box-style',
        cols: 6,
    },
    enableScss: {
        value: false,
        tab: 'scss',
        card: 'scss',
        cols: 12
    },
    scss: {
        type: 'code',
        value: '',
        tab: 'scss',
        card: 'scss',
        cols: 12,
        conditions: [{ property: 'enableScss', value: true }]
    }
};

export default ctaBanner;
