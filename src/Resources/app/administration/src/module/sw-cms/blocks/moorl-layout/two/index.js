import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-layout-two',
    label: 'sw-cms.blocks.moorl-layout.two',
    category: 'moorl-layout',
    component: 'sw-cms-block-moorl-layout-two',
    previewComponent: 'sw-cms-preview-block-moorl-layout-two',
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
        'slot-c': 'moorl-replacer',
        'slot-d': 'moorl-replacer'
    }
});
