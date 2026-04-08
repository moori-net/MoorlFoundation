import template from './index.html.twig';

Shopware.Component.register('moorl-price-field', {
    template,

    emits: ['update:value'],

    props: {
        label: {
            type: String,
            required: false
        },
        value: {
            type: Array,
            required: false,
            default: () => null
        },
        tax: {
            type: Object,
            required: true,
        },
        currency: {
            type: Object,
            required: true,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            cachedPriceForInstance: null,
        };
    },

    computed: {
        price: {
            get() {
                const prices = Array.isArray(this.value) ? this.value : [];
                const priceForCurrency = prices.find((price) => price.currencyId === this.currency.id);

                if (priceForCurrency) {
                    return [priceForCurrency];
                }

                return [{
                    gross: null,
                    currencyId: this.currency.id,
                    linked: true,
                    net: null,
                }];
            },

            set(newPurchasePrices) {
                const newPurchasePrice = Array.isArray(newPurchasePrices)
                    ? newPurchasePrices[0]
                    : newPurchasePrices;

                const prices = Array.isArray(this.value) ? [...this.value] : [];

                const isEmpty =
                    !newPurchasePrice ||
                    (newPurchasePrice.gross == null && newPurchasePrice.net == null);

                const index = prices.findIndex(
                    (price) => price.currencyId === this.currency.id
                );

                if (isEmpty) {
                    if (index !== -1) {
                        prices.splice(index, 1);
                    }
                } else if (index !== -1) {
                    prices.splice(index, 1, newPurchasePrice);
                } else {
                    prices.push(newPurchasePrice);
                }

                this.$emit('update:value', prices.length ? prices : null);
            }
        },
    },

    watch: {
        disabled(value) {
            const prices = Array.isArray(this.value) ? [...this.value] : [];
            const currentPrice = prices.find(
                (price) => price.currencyId === this.currency.id
            ) || null;

            if (value === true) {
                this.cachedPriceForInstance = currentPrice;
                this.$emit('update:value', null);
                return;
            }

            if (value === false && this.cachedPriceForInstance) {
                const filteredPrices = prices.filter(
                    (price) => price.currencyId !== this.currency.id
                );

                filteredPrices.push(this.cachedPriceForInstance);

                this.$emit('update:value', filteredPrices);
                this.cachedPriceForInstance = null;
            }
        }
    },

    created() {
        if (this.value === undefined) {
            this.$emit('update:value', null);
        }
    },

    methods: {
        priceChanged(price) {
            this.price = price;
        }
    }
});
