import template from './index.html.twig';
import './index.scss';
import defaultValue from './default.json';

const {Component} = Shopware;

Component.register('moorl-animation-container', {
    template,

    emits: ['update:item'],

    props: {
        item: {
            type: Object,
            required: true,
        }
    },

    created() {
        if (!this.item) {
            this.$emit('item:item', defaultValue);
        }
    },
});
