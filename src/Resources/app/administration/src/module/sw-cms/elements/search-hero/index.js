const Application = Shopware.Application;
import './component';
import './config';

Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'default-action-search',
    name: 'moorl-search-hero',
    label: 'sw-cms.elements.moorl-search-hero.title',
    component: 'sw-cms-el-moorl-search-hero',
    configComponent: 'sw-cms-el-config-moorl-search-hero',
    previewComponent: true,
    defaultConfig: {
        searchActive: {
            source: 'static',
            value: true
        },
        captionActive: {
            source: 'static',
            value: true
        },
        elementHeight: {
            source: 'static',
            value: '500px'
        },
        media: {
            source: 'static',
            value: null,
            required: true,
            entity: {
                name: 'media'
            }
        },
        backgroundAttachment: {
            source: 'static',
            value: 'not-fixed'
        },
        backgroundRepeat: {
            source: 'static',
            value: 'no-repeat'
        },
        backgroundSize: {
            source: 'static',
            value: 'cover'
        },
        backgroundPosition: {
            source: 'static',
            value: 'center top'
        },
        headline: {
            source: 'static',
            value: 'Lorem ipsum dolor'
        },
        subHeadline: {
            source: 'static',
            value: 'Lorem ipsum dolor sit amet, consetetur sadipscing'
        },
        textColor: {
            source: 'static',
            value: '#ffffff'
        },
        textShadowActive: {
            source: 'static',
            value: true
        }
    }
});
