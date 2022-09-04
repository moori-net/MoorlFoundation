const {Component} = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-preview-moorl-opening-hours', {
    template,
    data() {
        return {
            label: 'sw-cms.elements.moorl-opening-hours.name'
        }
    }
});
