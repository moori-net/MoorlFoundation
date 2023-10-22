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
            required: true
        },
        formOptions: {
            type: Array,
            required: true
        },
        config: {
            type: Object,
            required: true
        },
        clientCriteria: {
            type: Object,
            required: true
        },
    },

    computed: {
        description() {
            return this.$tc('moorl-flow-crm-mapping.description', 0, {
                name: this.name
            });
        },

        propertiesClientId() {
            return this.$tc('moorl-flow-crm-mapping.properties.clientId', 0, {
                name: this.name
            });
        },

        helpTextClientId() {
            return this.$tc('moorl-flow-crm-mapping.helpText.clientId', 0, {
                name: this.name
            });
        },

        propertiesFormId() {
            return this.$tc('moorl-flow-crm-mapping.properties.formId', 0, {
                name: this.name
            });
        },

        helpTextFormId() {
            return this.$tc('moorl-flow-crm-mapping.helpText.formId', 0, {
                name: this.name
            });
        },
    },

    methods: {
        onChangeClient() {
            this.$emit('change-client');
        },

        onChangeForm() {
            this.config.mapping = {};

            this.$emit('change-form');
        },

        onChangeMapping() {
            console.log(this.config);

            this.$emit('change-mapping');
        }
    }
});
