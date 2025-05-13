import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-grid-layout-five',
    label: 'sw-cms.blocks.moorl-grid-layout.five',
    category: 'moorl-grid-layout',
    component: 'sw-cms-block-moorl-grid-layout-five',
    previewComponent: 'sw-cms-preview-block-moorl-grid-layout-five',
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
    },
});
