import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'appflix-usp',
    label: 'sw-cms.blocks.appflix-usp.label',
    category: 'appflix-additional',
    component: 'sw-cms-block-appflix-usp',
    previewComponent: 'sw-cms-preview-appflix-usp',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px'
    },
    slots: {
        one: {
            type: 'appflix-usp'
        },
        two: {
            type: 'appflix-usp'
        },
        three: {
            type: 'appflix-usp'
        }
    }
});
