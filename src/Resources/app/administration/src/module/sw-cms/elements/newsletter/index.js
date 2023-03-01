const Application = Shopware.Application;
import './component';
import './config';

Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-paper-plane',
    name: 'moorl-newsletter',
    label: 'sw-cms.elements.moorl-newsletter.title',
    component: 'sw-cms-el-moorl-newsletter',
    configComponent: 'sw-cms-el-config-moorl-newsletter',
    previewComponent: true,
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
