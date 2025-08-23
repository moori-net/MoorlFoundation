import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-grid-layout-eleven',
    label: 'sw-cms.blocks.moorl-grid-layout.eleven',
    category: 'moorl-grid-layout',
    component: 'sw-cms-block-moorl-grid-layout-eleven',
    previewComponent: 'sw-cms-preview-block-moorl-grid-layout-eleven',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '0',
        marginLeft: '0',
        marginRight: '0',
        sizingMode: 'boxed',
    },
    slots: {
        'slot-a': 'moorl-replacer',
        'slot-b': 'moorl-replacer',
        'slot-c': 'moorl-replacer',
        'slot-d': 'moorl-replacer',
        'slot-e': 'moorl-replacer',
    },
});
