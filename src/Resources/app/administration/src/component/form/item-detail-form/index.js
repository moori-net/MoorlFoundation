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

        formStruct() {
            return this.formBuilderHelper.buildFormStruct();
        },

        defaultTab() {
            return this.formStruct.tabs[0].id;
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

    created() {
        this.createdComponent();
    },

    methods: {
        isVisible(field) {
            return MoorlFoundation.ConditionHelper.isVisible(field, this.item);
        },

        async loadCustomFieldSets() {
            if (this.item.customFields === undefined) {
                return Promise.resolve();
            }

            this.customFieldSets = await this.customFieldDataProviderService
                .getCustomFieldSets(this.entity);
        },

        async createdComponent() {
            await this.loadCustomFieldSets();

            this.formBuilderHelper.customFieldSets = this.customFieldSets;
        }
    }
});
