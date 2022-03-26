import template from './index.html.twig';
import './index.scss';

const {Component, Context} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('moorl-product-buy-list', {
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
    },

    computed: {
        productSearchCriteria() {
            const criteria = new Criteria(1, 25);
            criteria.addAssociation('options.group');
            criteria.addAssociation('cover');

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
