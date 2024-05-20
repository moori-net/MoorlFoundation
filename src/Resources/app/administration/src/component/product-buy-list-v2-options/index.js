const {Component} = Shopware;

import template from './index.html.twig';

Component.register('moorl-product-buy-list-v2-options', {
    template,

    emits: [
        'update:value'
    ],

    props: {
        value: {
            type: Object,
            required: true,
            default: {}
        },

        extended: {
            type: Boolean,
            default: false
        },

        layout: {
            type: Boolean,
            default: false
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            const value = Object.assign({
                enablePrices: true,
                enableAddToCart: false,
                enableVariantSwitch: false,
                enableAddToCartAll: true,
                enableSelection: false,
                enableAddToCartSingle: false,
                enableDirectUrl: true,
                layout: 'banner-content',
                minWidth: "330px",
                showBanner: true,
                showTitleDescription: true,
                showProductBuyList: true,
            }, this.value)

            if (this.value) {
                this.$emit('update:value', value);
            }
        }
    }
});
