const {Component} = Shopware;

import template from './sw-cms-sidebar.html.twig';

Component.override('sw-cms-sidebar', {
    template,

    data() {
        return {
            snippetPrefix: 'sw-cms.component.sw-cms-sidebar.',
        };
    }
});
