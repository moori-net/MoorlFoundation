import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-grid-layout-four',
    label: 'sw-cms.blocks.moorl-grid-layout.four',
    category: 'moorl-grid-layout',
    component: 'sw-cms-block-moorl-grid-layout-four',
    previewComponent: 'sw-cms-preview-block-moorl-grid-layout-four',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
    },
    slots: {
        'slot-a': 'moorl-replacer',
        'slot-b': 'moorl-replacer',
        'slot-c': 'moorl-replacer',
        'slot-d': 'moorl-replacer',
    },
});
