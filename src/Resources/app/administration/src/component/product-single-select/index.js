import template from './index.html.twig';
import './index.scss';

const {Component, Context} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('moorl-product-single-select', {
    template,

    props: ['value','label'],

    watch: {
        value: function () {
            this.$emit('input', this.value);
        }
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
