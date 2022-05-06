const Application = Shopware.Application;
import './component';
import './config';
import './preview';

const Criteria = Shopware.Data.Criteria;
const criteria = new Criteria();

Application.getContainer('service').cmsService.registerCmsElement({
    name: 'appflix-brand-slider',
    label: 'sw-cms.elements.appflix-brand-slider.title',
    component: 'sw-cms-el-appflix-brand-slider',
    configComponent: 'sw-cms-el-config-appflix-brand-slider',
    previewComponent: 'sw-cms-el-preview-appflix-brand-slider',
    defaultConfig: {
        brands: {
            source: 'static',
            value: [],
            required: true,
            entity: {
                name: 'product_manufacturer',
                criteria: criteria
            }
        },
        rotate: {
            source: 'static',
            value: false
        },
        elMinWidth: {
            source: 'static',
            value: '300px'
        },
        height: {
            source: 'static',
            value: '60px'
        },
        onlyImages: {
            source: 'static',
            value: false
        },
        useLink: {
            source: 'static',
            value: false
        }
    }
});
