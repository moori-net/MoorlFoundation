import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-cms-element-config-form', {
    template,

    emits: ['update'],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder')
    ],

    props: {
        entity: {
            type: String,
            required: false,
            default: undefined
        },
        componentName: {
            type: String,
            required: true
        },
        item: {
            type: Object,
            required: true,
        },
        cmsElementMapping: {
            type: Object,
            required: true,
        }
    },

    data() {
        return {
            formStruct: null
        };
    },

    computed: {
        formBuilderHelper() {
            return new MoorlFoundation.FormBuilderHelper({
                entity: this.entity,
                item: this.item,
                tc: this.$tc,
                componentName: this.componentName,
                cmsElementMapping: this.cmsElementMapping
            });
        },

        translationHelper() {
            return this.formBuilderHelper.translationHelper;
        },

        defaultTab() {
            return this.formStruct?.tabs?.[0]?.id ?? null;
        },

        fieldModels() {
            return new Proxy({}, {
                get: (_, prop) => {
                    return this.item?.[prop].value;
                },
                set: (_, prop, value) => {
                    this.item[prop].value = value;
                    return true;
                }
            });
        }
    },

    watch: {
        item: {
            handler() {
                this.formStruct = this.formBuilderHelper.buildFormStruct();
            },
            deep: false
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        fieldAttributes(field) {
            return field.attributes;
        },

        isVisible(field) {
            return MoorlFoundation.ConditionHelper.isVisible(field, this.item);
        },

        isDisabled(field) {
            return !this.isVisible(field);
        },

        isDisabledTab(tab) {
            for (const card of tab.cards) {
                if (!this.isDisabledCard(card)) {
                    return false;
                }
            }
            return true;
        },

        isDisabledCard(card) {
            for (const field of card.fields) {
                if (this.isVisible(field)) {
                    return false;
                }
            }
            return true;
        },

        getStyle(field) {
            return {
                'grid-column': `span ${field.cols}`,
                /*order: field.order*/
            }
        },

        async createdComponent() {
            this.formStruct = this.formBuilderHelper.buildFormStruct();
        }
    }
});
