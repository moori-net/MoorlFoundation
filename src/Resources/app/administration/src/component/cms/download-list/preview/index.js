const {Component} = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-preview-moorl-download-list', {
    template,
    data() {
        return {
            label: 'sw-cms.elements.moorl-download-list.name'
        }
    }
});
