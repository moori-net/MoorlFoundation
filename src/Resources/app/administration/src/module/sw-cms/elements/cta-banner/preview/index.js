const {Component} = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-preview-appflix-cta-banner', {
    template,

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.appflix-cta-banner.'
        };
    },
});
