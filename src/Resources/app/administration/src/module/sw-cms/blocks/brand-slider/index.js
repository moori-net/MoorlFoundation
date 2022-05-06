import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'appflix-brand-slider',
    label: 'sw-cms.blocks.appflix-brand-slider.label',
    category: 'appflix-additional',
    component: 'sw-cms-block-appflix-brand-slider',
    previewComponent: 'sw-cms-preview-appflix-brand-slider',
    defaultConfig: {
        marginBottom: '0',
        marginTop: '0',
        marginLeft: '0',
        marginRight: '0',
        sizingMode: 'boxed'
    },
    slots: {
        one: {
            type: 'appflix-brand-slider'
        }
    }
});
