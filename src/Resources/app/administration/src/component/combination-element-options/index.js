import template from './index.html.twig';

Shopware.Component.register('moorl-combination-element-options', {
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
                enableDirectUrl: true,
            });

            if (this.isEnabled('countdown')) {
                Object.assign(value,{
                    showCountdown: true,
                    countdownType: 'countdown',
                });
            }

            if (this.isEnabled('stock')) {
                Object.assign(value,{
                    showStock: true,
                    stockType: 'text',
                });
            }

            if (this.isEnabled('hotspots')) {
                Object.assign(value,{
                    showHotspots: true,
                    hotspotAnimation: 'pulse',
                });
            }

            if (this.isEnabled('product-buy-list')) {
                Object.assign(value,{
                    enableAddToCart: false,
                    enableVariantSwitch: false,
                    enableAddToCartAll: true,
                    enableSelection: false,
                    enableAddToCartSingle: false,
                    addToCartSingleIcon: 'plus',
                    addToCartAllIcon: '',
                    showProductBuyList: true,
                });
            }

            if (this.isEnabled('layout')) {
                Object.assign(value,{
                    layout: 'banner-content',
                    minWidth: "330px",
                    showBanner: true,
                    showTitleDescription: true,
                    classAttribute: "border bg-light p-3",
                    idAttribute: null,
                });
            }

            if (this.value) {
                Object.assign(value, this.value);

                this.$emit('update:value', value);
            }
        }
    }
});
