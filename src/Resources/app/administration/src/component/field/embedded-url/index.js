import template from './index.html.twig';

Shopware.Component.register('moorl-embedded-url-field', {
    template,

    emits: ['update:value'],

    props: {
        value: {
            type: String,
            required: false,
            default: undefined,
        },
        label: {
            type: String,
            required: false,
            default: undefined,
        },
        placeholder: {
            type: String,
            required: false,
            default: undefined,
        },
        helpText: {
            type: String,
            required: false,
            default: undefined,
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        }
    },

    computed: {
        vBind() {
            return {
                label: this.label,
                helpText: this.helpText,
                placeholder: this.placeholder,
                disabled: this.disabled
            };
        },

        currentValue: {
            get() {
                return this.value;
            },
            set(newValue) {
                this.$emit('update:value', newValue ?? null);
            },
        },
    }
});
