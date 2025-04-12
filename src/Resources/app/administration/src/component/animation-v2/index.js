const {Component} = Shopware;

import template from './index.html.twig';

Component.register('moorl-animation-v2', {
    template,

    props: {
        value: {
            type: Object,
            required: true,
            default: {}
        }
    }
});
