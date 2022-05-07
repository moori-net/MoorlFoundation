const Application = Shopware.Application;
import './component';
import './config';
import './preview';

Application.getContainer('service').cmsService.registerCmsElement({
    hidden: true,
    name: 'appflix-newsletter',
    label: 'sw-cms.elements.appflix-newsletter.title',
    component: 'sw-cms-el-appflix-newsletter',
    configComponent: 'sw-cms-el-config-appflix-newsletter',
    previewComponent: 'sw-cms-el-preview-appflix-newsletter',
    defaultConfig: {
        optin: {
            source: 'static',
            value: true
        },
        buttonTextColor:{
            source: 'static',
            value: '#ffffff'
        },
        buttonBackground:{
            source: 'static',
            value: '#4495c0'
        },
        placeholder:{
            source: 'static',
            value: 'Enter E-Mail ...'
        },
        buttonText:{
            source: 'static',
            value: 'Register'
        },
        privacyColor:{
            source: 'static',
            value: '#ffffff'
        }
    }
});
