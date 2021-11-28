import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-interactive-slider',
    label: 'sw-cms.blocks.moorl-slider.interactive',
    category: 'moorl-slider',
    component: 'sw-cms-block-moorl-interactive-slider',
    previewComponent: 'sw-cms-preview-moorl-interactive-slider',
    defaultConfig: {
        marginBottom: '0',
        marginTop: '0',
        marginLeft: '0',
        marginRight: '0',
        sizingMode: 'boxed'
    },
    slots: {
        'slot-a': 'moorl-replacer'
    }
});
