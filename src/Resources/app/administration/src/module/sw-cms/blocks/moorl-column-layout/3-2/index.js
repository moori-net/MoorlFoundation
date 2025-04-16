import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-column-layout-3-2',
    label: 'sw-cms.blocks.moorl-column-layout.3-2',
    category: 'moorl-column-layout',
    component: 'sw-cms-block-moorl-column-layout-3-2',
    previewComponent: 'sw-cms-preview-block-moorl-column-layout-3-2',
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
    },
});
