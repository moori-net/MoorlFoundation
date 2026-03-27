import template from './index.html.twig';

Shopware.Component.register('moorl-icon-field', {
    template,

    emits: ['update:value'],

    props: {
        value: {
            type: String,
            required: false,
            default: null,
        }
    },

    computed: {
        currentValue: {
            get() {
                return this.value;
            },
            set(newValue) {
                this.$emit('update:value', newValue ?? null);
            },
        },
    },
});
