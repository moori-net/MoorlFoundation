import template from './index.html.twig';

const {Criteria} = Shopware.Data;

Shopware.Component.register('moorl-product-multi-select-field', {
    template,

    emits: ['update:entityCollection'],

    props: {
        label: {
            type: String,
            required: false
        },
        entityCollection: {
            type: Array,
            required: true,
            default: []
        }
    },

    computed: {
        productCollection: {
            get() {
                return this.entityCollection;
            },
            set(newValue) {
                this.$emit('update:entityCollection', newValue ?? null);
            },
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
    }
});
