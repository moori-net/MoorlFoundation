import template from './index.html.twig';

Shopware.Component.register('moorl-form-fields', {
    template,

    props: {
        item: {
            type: Object,
            required: true
        },
        fields: {
            type: Array,
            required: true
        },
        fieldModels: {
            type: Object,
            required: true
        },
        isVisibleComponent: {
            type: Function,
            required: true
        },
        getStyle: {
            type: Function,
            required: true
        },
        fieldAttributes: {
            type: Function,
            required: true
        },
        getError: {
            type: Function,
            required: true
        },
        cmsElement: {
            type: Object,
            required: false,
            default: undefined
        }
    },

    computed: {
        hasSwCmsInheritWrapperComponent() {
            return Shopware.Component.getComponentRegistry().has('sw-cms-inherit-wrapper');
        }
    },

    methods: {
        componentProps(field, disabled) {
            const modelProp = field.model ?? "modelValue";
            const updateEvent = `onUpdate:${modelProp}`;

            const props = {
                ...this.fieldAttributesDynamicLabel(field),
                error: this.getError(field),
                [modelProp]: this.fieldModels[field.path],
                [updateEvent]: (val) => (this.fieldModels[field.path] = val),
            };

            if (disabled) {
                props.disabled = disabled;
            }

            return props;
        },

        fieldWrapperLabel(field) {
            if (field.componentName === 'mt-switch' || field.componentName === 'mt-checkbox') {
                return undefined;
            }

            return field.label;
        },

        fieldAttributesDynamicLabel(field) {
            if (
                !this.cmsElement ||
                field.componentName === 'mt-switch' ||
                field.componentName === 'mt-checkbox' ||
                (!this.hasSwCmsInheritWrapperComponent && !field.cmsMappingField)
            ) {
                return this.fieldAttributes(field);
            }

            return {
                ...this.fieldAttributes(field),
                label: undefined,
                variant: 'small'
            };
        },
    }
});
