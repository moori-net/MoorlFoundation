import template from './index.html.twig';

const { Component } = Shopware;

Component.register('sw-cms-el-preview-appflix-table-registration', {
    template,

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.appflix-table-registration.'
        };
    },
});
