import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-item-detail-form', {
    template,

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder')
    ],

    inject: [
        'customFieldDataProviderService',
    ],

    props: {
        entity: {
            type: String,
            required: true,
        },
        componentName: {
            type: String,
            required: true
        },
        item: {
            type: Object,
            required: true,
        },
        tabRouting: {
            type: Boolean,
            required: false,
            default: true
        }
    },

    data() {
        return {
            customFieldSets: null,
            formStruct: null
        };
    },

    computed: {
        formBuilderHelper() {
            return new MoorlFoundation.FormBuilderHelper({
                item: this.item,
                entity: this.entity,
                tc: this.$tc,
                componentName: this.componentName
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
                    return this.item.extensions?.[prop] ?? this.item?.[prop];
                },
                set: (_, prop, value) => {
                    if (this.item.extensions?.hasOwnProperty(prop)) {
                        this.item.extensions[prop] = value;
                    } else {
                        this.item[prop] = value;
                    }
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
            return {
                ...field.attributes,
                disabled: this.isDisabled(field)
            };
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
                order: field.order
            }
        },

        async loadCustomFieldSets() {
            if (this.item.customFields === undefined) {
                return Promise.resolve();
            }

            this.customFieldSets = await this.customFieldDataProviderService
                .getCustomFieldSets(this.entity);
        },

        async createdComponent() {
            this.formStruct = this.formBuilderHelper.buildFormStruct();

            await this.loadCustomFieldSets();

            this.formBuilderHelper.customFieldSets = this.customFieldSets;
        }
    }
});
