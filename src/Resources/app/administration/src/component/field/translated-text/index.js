import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-translated-text-field', {
    template,

    emits: ['update:value'],

    props: {
        value: {
            type: Object,
            required: false,
            default: () => ({})
        }
    },

    data() {
        return {
            languageId: Shopware.Context.api.languageId
        };
    },

    computed: {
        currentValue: {
            get() {
                return this.value?.[this.languageId] ?? null;
            },
            set(newValue) {
                const updatedValue = { ...this.value };

                if (newValue === null || newValue === undefined || newValue === '') {
                    delete updatedValue[this.languageId];
                } else {
                    updatedValue[this.languageId] = newValue;
                }

                this.$emit('update:value', updatedValue);
            }
        },
    },

    created() {
        this.initDefaultValue();
    },

    methods: {
        initDefaultValue() {
            if (!this.currentValue) {
                this.currentValue = this.$attrs.placeholder ?? null;
            }
        },

        onChangeLanguage(languageId) {
            this.languageId = languageId;
        }
    }
});
