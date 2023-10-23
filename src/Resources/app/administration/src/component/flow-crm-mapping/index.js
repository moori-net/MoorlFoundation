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
        elementFieldNames: {
            type: Array,
            required: false,
            default: []
        }
    },

    computed: {
        description() {
            if (this.elementFieldNames.length) {
                return this.$tc('moorl-flow-crm-mapping.descriptionAlt', 0, {
                    name: this.name
                });
            }

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

    created() {
        this.createdComponent();
    },

    watch: {
        activeFormFields: {
            handler(value) {
                if (!value || !value.length) {
                    return;
                }

                if (!this.config?.mapping?.length) {
                    this.config.mapping = value;
                }
            },
        },
    },

    methods: {
        createdComponent() {
            if (!this.config?.mapping?.length) {
                this.config.mapping = this.activeFormFields;
            }

            this.$emit('change-client');
        },

        onChangeClient() {
            this.$emit('change-client');
        },

        onChangeForm() {
            this.config.mapping = [];

            this.$emit('change-form');
        },

        onChangeMapping() {
            this.$emit('change-mapping');
        }
    }
});
