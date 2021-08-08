import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-slider-2',
    label: 'sw-cms.blocks.moorl-slider.2',
    category: 'moorl-slider',
    component: 'sw-cms-block-moorl-slider-2',
    previewComponent: 'sw-cms-preview-moorl-slider-2',
    defaultConfig: {
        marginBottom: '0',
        marginTop: '0',
        marginLeft: '0',
        marginRight: '0',
        sizingMode: 'boxed'
    },
    slots: {
        'slot-a': 'moorl-replacer',
        'slot-b': 'moorl-replacer'
    }
});
