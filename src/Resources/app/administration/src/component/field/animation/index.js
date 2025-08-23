import template from './index.html.twig';
import './index.scss';
import defaultValue from './default.json';

Shopware.Component.register('moorl-animation-field', {
    template,

    emits: ['update:item'],

    props: {
        item: {
            type: Object,
            required: true,
        },
    },

    created() {
        if (!this.item) {
            this.$emit('update:item', defaultValue);
        }
    },
});
