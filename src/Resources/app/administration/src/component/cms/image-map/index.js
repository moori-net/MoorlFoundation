const Application = Shopware.Application;
import './component';
import './config';

Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-search',
    name: 'moorl-image-map',
    label: 'sw-cms.elements.moorl-image-map.title',
    component: 'sw-cms-el-moorl-image-map',
    configComponent: 'sw-cms-el-config-moorl-image-map',
    previewComponent: true,
    defaultConfig: {
        searchType: {
            source: 'static',
            value: 'search'
        },
        categoryId: {
            source: 'static',
            value: null
        },
        captionActive: {
            source: 'static',
            value: true
        },
        mediaActive: {
            source: 'static',
            value: true
        },
        height: {
            source: 'static',
            value: '300px'
        },
        media: {
            source: 'static',
            value: null,
            entity: {
                name: 'media'
            }
        },
        backgroundFixed: {
            source: 'static',
            value: false,
        },
        backgroundVerticalAlign: {
            source: 'static',
            value: 'center'
        },
        backgroundHorizontalAlign: {
            source: 'static',
            value: 'center'
        },
        backgroundDisplayMode: {
            source: 'static',
            value: 'cover'
        },
        backgroundSizeX: {
            source: 'static',
            value: '300px'
        },
        backgroundSizeY: {
            source: 'static',
            value: '300px'
        },
        title: {
            source: 'static',
            value: 'Lorem ipsum dolor'
        },
        content: {
            source: 'static',
            value: 'Lorem ipsum dolor sit amet, consetetur sadipscing'
        },
        textShadowActive: {
            source: 'static',
            value: true
        },
        boxVerticalAlign: {
            source: 'static',
            value: 'center'
        },
        boxHorizontalAlign: {
            source: 'static',
            value: 'center'
        },
        boxTextAlign: {
            source: 'static',
            value: 'left'
        },
        boxWidth: {
            source: 'static',
            value: 'auto'
        },
        boxHeight: {
            source: 'static',
            value: 'auto'
        },
        boxMargin: {
            source: 'static',
            value: '20px'
        },
        boxPadding: {
            source: 'static',
            value: '15px'
        },
        boxColor: {
            source: 'static',
            value: '#FFFFFF'
        },
        boxBackground: {
            source: 'static',
            value: 'rgba(255,255,255,0.7)'
        },
        boxMaxWidth: {
            source: 'static',
            value: false
        },
        boxBorderRadius: {
            source: 'static',
            value: '8px'
        },
    }
});
