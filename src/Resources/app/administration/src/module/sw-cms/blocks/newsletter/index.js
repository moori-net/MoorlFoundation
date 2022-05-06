import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'appflix-newsletter',
    label: 'sw-cms.blocks.appflix-newsletter.label',
    category: 'appflix-additional',
    component: 'sw-cms-block-appflix-newsletter',
    previewComponent: 'sw-cms-preview-appflix-newsletter',
    defaultConfig: {
        marginBottom: '25px',
        marginTop: '25px',
        marginLeft: '0',
        marginRight: '0',
        sizingMode: 'boxed'
    },
    slots: {
        one: {
            type: 'text'
        },
        two: {
            type: 'appflix-newsletter'
        }
    }
});
