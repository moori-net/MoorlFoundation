import template from './index.html.twig';

const { snakeCase } = Shopware.Utils.string;

Shopware.Component.register('moorl-flow-crm-mapping', {
    template,

    props: {
        name: {
            type: String,
            required: false,
            default: 'Hubspot',
        },
        activeFormFields: {
            type: Array,
            required: true,
        },
        formOptions: {
            type: Array,
            required: true,
        },
        config: {
            type: Object,
            required: true,
        },
        clientCriteria: {
            type: Object,
            required: true,
        },
        elementFieldNames: {
            type: Array,
            required: false,
            default: [],
        },
    },

    data() {
        return {
            objects: {
                contactFormData: [
                    'email',
                    'firstName',
                    'lastName',
                    'phone',
                    'subject',
                    'comment',
                ],
                reviewFormData: [
                    'email',
                    'firstName',
                    'lastName',
                    'name',
                    'points',
                    'title',
                    'content',
                ],
            },
        };
    },

    computed: {
        triggerEvent() {
            return Store.get('swFlowState')?.triggerEvent;
        },

        dataSelection() {
            if (this.elementFieldNames.length) {
                return this.elementFieldNames;
            }

            if (!this.triggerEvent) {
                return [];
            }

            return this.getEntityProperty(this.triggerEvent.data).concat(
                this.getObjectProperty(this.triggerEvent.data)
            );
        },

        description() {
            return this.$tc('moorl-flow-crm-mapping.description', 0, {
                name: this.name,
            });
        },

        propertiesClientId() {
            return this.$tc('moorl-flow-crm-mapping.properties.clientId', 0, {
                name: this.name,
            });
        },

        helpTextClientId() {
            return this.$tc('moorl-flow-crm-mapping.helpText.clientId', 0, {
                name: this.name,
            });
        },

        propertiesFormId() {
            return this.$tc('moorl-flow-crm-mapping.properties.formId', 0, {
                name: this.name,
            });
        },

        helpTextFormId() {
            return this.$tc('moorl-flow-crm-mapping.helpText.formId', 0, {
                name: this.name,
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

        getObjectProperty(data) {
            const objects = this.objects;
            const stored = [];

            Object.keys(data).forEach((key) => {
                Object.keys(objects).forEach((objKey) => {
                    if (key === objKey) {
                        objects[key].forEach((objVal) => {
                            stored.push({
                                value: `{{ ${objKey}.${objVal} }}`,
                                label: `${objKey}.${objVal}`,
                            });
                        });
                    }
                });
            });

            return stored;
        },

        getEntityProperty(data) {
            const entities = [];

            Object.keys(data).forEach((key) => {
                if (data[key].type === 'entity') {
                    entities.push(key);
                }
            });

            if (entities.length === 0) {
                return [];
            }

            return entities.reduce((result, entity) => {
                const entityName = snakeCase(entity);
                const properties = Shopware.EntityDefinition.get(
                    entityName
                ).filterProperties((property) => {
                    return Shopware.EntityDefinition.getScalarTypes().includes(
                        property.type
                    );
                });

                return result.concat(
                    Object.keys(properties).map((property) => {
                        return {
                            value: `{{ ${entity}.${property} }}`,
                            label: `${entity}.${property}`,
                        };
                    })
                );
            }, []);
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
        },
    },
});
