import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'moorl-layout-one',
    label: 'sw-cms.blocks.moorl-layout.one',
    category: 'moorl-layout',
    component: 'sw-cms-block-moorl-layout-one',
    previewComponent: 'sw-cms-preview-block-moorl-layout-one',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        'slot-a': 'text',
        'slot-b': 'text',
        'slot-c': 'text',
        'slot-d': 'text'
    }
});
