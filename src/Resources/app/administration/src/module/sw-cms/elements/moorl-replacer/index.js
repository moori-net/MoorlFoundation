const Application = Shopware.Application;
import './component';

Application.getContainer('service').cmsService.registerCmsElement({
    name: 'moorl-replacer',
    label: 'sw-cms.elements.moorl-replacer.title',
    component: 'sw-cms-el-moorl-replacer',
    defaultConfig: null
});