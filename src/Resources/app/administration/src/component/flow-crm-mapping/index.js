import template from './index.html.twig';
import './index.scss';

const { Component } = Shopware;

Component.register('moorl-flow-crm-mapping', {
    template,

    props: {
        name: {
            type: String,
            required: false,
            default: 'Hubspot'
        },
        activeFormFields: {
            type: Array,
            required: false,
            default: [],
        },
        mapping: {
            type: Object,
            required: true
        },
    },

    computed: {
        description() {
            return this.$tc('moorl-flow-crm-mapping.description', 0, {
                name: this.name
            });
        }
    }
});
