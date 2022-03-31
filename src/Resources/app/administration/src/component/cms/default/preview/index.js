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
            if (this.element.color) {
                return `background: linear-gradient(transparent 50%, ${this.element.color} 50%);`
            }
        }
    }
});
