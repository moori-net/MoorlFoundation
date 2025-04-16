import template from './index.html.twig';
import './index.scss';

const {Criteria} = Shopware.Data;

Shopware.Component.register('moorl-entity-form-element', {
    inject: ['repositoryFactory'],

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
        repository() {
            return this.repositoryFactory.create(this.column.entity);
        },

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
                    this.value.price = cPrice;
                    return cPrice;
                }
                return price;
            },
            set(newValue) {
                //this.value.price = newValue || null;
                this.value.price = newValue || null;
            }
        },

        sortingSearchCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addFilter(Criteria.equals('entity', this.column?.flags?.moorl_edit_field_options?.entity));

            return criteria;
        },

        productSearchCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('options.group');

            return criteria;
        },

        productSearchContext() {
            return {
                ...Shopware.Context.api,
                inheritance: true
            };
        }
    },
});
