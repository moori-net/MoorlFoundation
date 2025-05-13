import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-grid-layout-ten',
    label: 'sw-cms.blocks.moorl-grid-layout.ten',
    category: 'moorl-grid-layout',
    component: 'sw-cms-block-moorl-grid-layout-ten',
    previewComponent: 'sw-cms-preview-block-moorl-grid-layout-ten',
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
