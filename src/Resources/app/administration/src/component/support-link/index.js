const {Component} = Shopware;

import template from './index.html.twig';

Component.register('moorl-support-link', {
    template,

    props: {
        path: {
            type: String,
            required: true
        }
    },

    computed: {
        supportLink() {
            return this.$tc('moorl-support-link.supportLink') + this.path;
        }
    }
});
