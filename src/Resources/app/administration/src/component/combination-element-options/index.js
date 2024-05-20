const {Component} = Shopware;

import template from './index.html.twig';

Component.register('moorl-combination-element-options', {
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

        options: {
            type: Array,
            default: ['countdown', 'stock', 'hotspots', 'product-buy-list', 'layout']
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        isEnabled(option) {
            return this.options.includes(option);
        },

        createdComponent() {
            const value = Object.assign({
                enablePrices: true,
                enableAddToCart: false,
                enableVariantSwitch: false,
                enableAddToCartAll: true,
                enableSelection: false,
                enableAddToCartSingle: false,
                addToCartSingleIcon: 'plus',
                addToCartAllIcon: '',
                enableDirectUrl: true,
                layout: 'banner-content',
                hotspotAnimation: 'pulse',
                minWidth: "330px",
                showBanner: true,
                showTitleDescription: true,
                showProductBuyList: true,
                showHotspots: true,
                showStock: true,
                stockType: 'text',
                showCountdown: true,
                countdownType: 'countdown',
            }, this.value)

            if (this.value) {
                this.$emit('update:value', value);
            }
        }
    }
});
