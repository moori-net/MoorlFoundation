import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-slider-3',
    label: 'sw-cms.blocks.moorl-slider.3',
    category: 'moorl-slider',
    component: 'sw-cms-block-moorl-slider-3',
    previewComponent: 'sw-cms-preview-moorl-slider-3',
    defaultConfig: {
        marginBottom: '0',
        marginTop: '0',
        marginLeft: '0',
        marginRight: '0',
        sizingMode: 'boxed'
    },
    slots: {
        'slot-a': {
            type: 'text'
        },
        'slot-b': {
            type: 'text'
        },
        'slot-c': {
            type: 'text'
        }
    }
});
