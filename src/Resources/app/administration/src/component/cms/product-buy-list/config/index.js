const {Component, Mixin} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-moorl-product-buy-list', {
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
            productCollection: null
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

            if (this.element.config.products.value.length <= 0) {
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
            this.element.config.products.value = this.productCollection.getIds();
            this.$set(this.element.data, 'products', this.productCollection);
            this.$emit('products-change');
        }
    }
});
