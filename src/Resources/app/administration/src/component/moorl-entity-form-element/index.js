import template from './index.html.twig';
import './index.scss';

const {Component, Context} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('moorl-entity-form-element', {
    template,

    props: {
        column: {
            type: Object,
            required: true
        },
        value: {
            type: Object,
            required: true
        },
        snippetSrc: {
            type: String,
            required: false,
            default: 'moorl-foundation'
        },
        /* Handling for prices */
        tax: {
            type: Object,
            required: false
        },
        defaultCurrency: {
            type: Object,
            required: false
        },
    },

    computed: {
        price: {
            get() {
                let price = []
                if (this.value && Array.isArray(this.value.price)) {
                    price = [...this.value.price];
                } else {
                    const cPrice = {};
                    cPrice[`c${this.defaultCurrency.id}`] = {
                        net: 0,
                        gross: 0,
                        linked: true,
                        currencyId: this.defaultCurrency.id
                    }
                    this.$set(this.value, 'price', cPrice);
                    return cPrice;
                }
                return price;
            },
            set(newValue) {
                //this.$set(this.value, 'price', newValue || null);
                this.value.price = newValue || null;
            }
        },

        productSearchCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('options.group');

            return criteria;
        },

        productSearchContext() {
            return {
                ...Context.api,
                inheritance: true
            };
        }
    },
});
