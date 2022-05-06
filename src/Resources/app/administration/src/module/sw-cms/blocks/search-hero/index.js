import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'appflix-search-hero',
    label: 'sw-cms.blocks.appflix-search-hero.label',
    category: 'appflix-additional',
    component: 'sw-cms-block-appflix-search-hero',
    previewComponent: 'sw-cms-preview-appflix-search-hero',
    defaultConfig: {
        marginBottom: '0',
        marginTop: '0',
        marginLeft: '0',
        marginRight: '0'
    },
    slots: {
        one: {
            type: 'appflix-search-hero'
        }
    }
});
