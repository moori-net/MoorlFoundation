import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-slider-4',
    label: 'sw-cms.blocks.moorl-slider.4',
    category: 'moorl-slider',
    component: 'sw-cms-block-moorl-slider-4',
    previewComponent: 'sw-cms-preview-moorl-slider-4',
    defaultConfig: {
        marginBottom: '0',
        marginTop: '0',
        marginLeft: '0',
        marginRight: '0',
        sizingMode: 'boxed'
    },
    slots: {
        'slot-a': 'moorl-replacer',
        'slot-b': 'moorl-replacer',
        'slot-c': 'moorl-replacer',
        'slot-d': 'moorl-replacer'
    }
});
