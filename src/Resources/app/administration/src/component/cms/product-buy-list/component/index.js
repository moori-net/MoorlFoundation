const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-moorl-product-buy-list', {
    template,

    props: {
        element: {
            type: Object,
            required: false,
            default: null,
        }
    },

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            items: [],
            criteria: new Criteria(1, 12)
        };
    },

    computed: {
        itemClass() {
            let className = "enable-add-to-cart";

            if (this.element.config.enableAddToCartAll.value) {
                className += "-all";
            }
            if (this.element.config.enableAddToCartSingle.value) {
                className += "-single";
            }

            return className;
        },

        priceTotal() {
            let price = 0;

            if (this.element.data.products) {
                this.element.data.products.forEach(function (product) {
                    if (product.price) {
                        price = price + product.price[0].gross;
                    }
                });
            }

            return price;
        },

        selectedItems() {
            let selectedItems = 0;

            if (this.element.data.products) {
                return this.element.data.products.length;
            }

            return selectedItems;
        }
    },

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (Object.keys(this.element.config).length) {
                return;
            }

            this.initElementConfig('moorl-product-buy-list');
            this.initElementData('moorl-product-buy-list');
        }
    },

    filters: {
        numberFormat: function (value) {
            return new Intl.NumberFormat('de-DE', {style: 'currency', currency: 'EUR'}).format(value)
        }
    }
});
