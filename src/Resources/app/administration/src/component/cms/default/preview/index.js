const {Component} = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-preview-moorl-default', {
    template,

    props: {
        element: {
            type: Object,
            required: true
        },
        plugin: {
            type: Object,
            required: true
        }
    },

    computed: {
        style() {
            return null;
        }
    }
});
