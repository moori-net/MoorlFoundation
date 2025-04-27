import template from './index.html.twig';

Shopware.Component.register('moorl-price-field', {
    template,

    emits: ['update:value'],

    props: {
        value: {
            type: Array,
            required: true,
            default: []
        },
        tax: {
            type: Object,
            required: true,
        },
        currency: {
            type: Object,
            required: true,
        },
    },

    computed: {
        price: {
            get() {
                const priceForCurrency = this.value.find((price) => price.currencyId === this.currency.id);
                if (priceForCurrency) {
                    return [priceForCurrency];
                }

                return [
                    {
                        gross: null,
                        currencyId: this.currency.id,
                        linked: true,
                        net: null,
                    },
                ];
            },

            set(newPurchasePrice) {
                let priceForCurrency = this.value.find((price) => price.currencyId === newPurchasePrice.currencyId);
                if (priceForCurrency) {
                    priceForCurrency = newPurchasePrice;
                } else {
                    // eslint-disable-next-line vue/no-mutating-props
                    this.value.push(newPurchasePrice);
                }

                this.$emit('update:value', this.value);
            },
        },
    },



    methods: {
        priceChanged(price) {
            this.price = price;
        }
    }
});
