const {Criteria, EntityCollection} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-product-buy-list', {
    template,

    props: {
        element: {
            type: Object,
            required: false,
            default: null,
        }
    },

    mixins: [
        Shopware.Mixin.getByName('cms-element')
    ],

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            items: [],
            productCollection: null,
            criteria: new Criteria(1, 12)
        };
    },

    computed: {
        productRepository() {
            return this.repositoryFactory.create('product');
        },

        products() {
            if (this.element.data && this.element.data.products && this.element.data.products.length > 0) {
                return this.element.data.products;
            }

            return null;
        },

        productSearchCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('options.group');
            criteria.addAssociation('cover');

            return criteria;
        },

        productSearchContext() {
            const context = Object.assign({}, Shopware.Context.api);
            context.inheritance = true;

            return context;
        },

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
            if (!Object.keys(this.element.config).length) {
                this.initElementConfig('moorl-product-buy-list');
                this.initElementData('moorl-product-buy-list');
            }

            this.productCollection = new EntityCollection('/product', 'product', Shopware.Context.api);

            if (!Array.isArray(this.element.config.products.value)) {
                return;
            }

            const criteria = new Criteria(1, 25);
            criteria.addAssociation('options.group');
            criteria.addAssociation('cover');
            criteria.setIds(this.element.config.products.value);

            this.productRepository.search(criteria, this.productSearchContext)
                .then(result => {
                    this.productCollection = result;
                    this.onProductsChange();
                });
        },

        onProductsChange() {
            if (!this.element.config?.products) {
                this.initElementConfig('moorl-product-buy-list');
            }
            if (!this.element.data?.products) {
                this.initElementData('moorl-product-buy-list');
            }

            this.element.config.products.value = this.productCollection.getIds();
            this.element.data.products = this.productCollection;
        }
    },

    filters: {
        numberFormat: function (value) {
            return new Intl.NumberFormat('de-DE', {style: 'currency', currency: 'EUR'}).format(value)
        }
    }
});
