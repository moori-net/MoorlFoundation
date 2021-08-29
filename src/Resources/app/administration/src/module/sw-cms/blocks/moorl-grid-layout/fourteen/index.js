import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-grid-layout-fourteen',
    label: 'sw-cms.blocks.moorl-grid-layout.fourteen',
    category: 'moorl-grid-layout',
    component: 'sw-cms-block-moorl-grid-layout-fourteen',
    previewComponent: 'sw-cms-preview-block-moorl-grid-layout-fourteen',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        'slot-a': 'moorl-replacer',
        'slot-b': 'moorl-replacer',
        'slot-c': 'moorl-replacer'
    }
});
