import './component';

Shopware.Application.getContainer('service').cmsService.registerCmsElement({
    plugin: 'MoorlFoundation',
    icon: 'regular-repeat',
    name: 'moorl-replacer',
    label: 'sw-cms.elements.moorl-replacer.title',
    component: 'sw-cms-el-moorl-replacer',
    previewComponent: true
});
