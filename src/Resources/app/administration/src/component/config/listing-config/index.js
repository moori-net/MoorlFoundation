import template from './index.html.twig';
import defaultValue from './default.json';

Shopware.Component.register('moorl-listing-config', {
    template,

    emits: ['update:value'],

    props: {
        value: {
            type: Object,
            required: false,
            default: null
        }
    },

    data() {
        return {
            isLoading: true
        };
    },

    created() {
        if (!this.value) {
            this.$emit('update:value', defaultValue);
        }

        this.isLoading = false;
    },
});
