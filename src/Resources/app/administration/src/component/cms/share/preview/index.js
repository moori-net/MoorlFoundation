const {Component} = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-preview-moorl-share', {
    template,
    data() {
        return {
            label: 'sw-cms.elements.moorl-share.name'
        }
    }
});
