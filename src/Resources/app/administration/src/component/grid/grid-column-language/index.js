Shopware.Component.extend('moorl-grid-column-language', 'sw-data-grid-column-boolean', {
    props: {
        value: {
            type: Array,
            required: true,
            default: [],
        },
        languageId: {
            type: String,
            required: true,
            default: null,
        }
    },

    computed: {
        currentValue: {
            get() {
                return this.value.includes(this.languageId);
            },
            set(newValue) {
                const updated = new Set(this.value);

                newValue ? updated.add(this.languageId) : updated.delete(this.languageId);

                this.$emit('update:value', [...updated]);
            }
        },
    },
});
