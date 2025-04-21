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
            if (!field.conditions || field.conditions.length === 0) {
                return true;
            }

            const compare = (a, b, operator) => {
                switch (operator) {
                    case 'eq':
                    case '==': return a == b;
                    case '===': return a === b;
                    case '!=': return a != b;
                    case '!==': return a !== b;
                    case 'gt':
                    case '>': return a > b;
                    case 'lt':
                    case '<': return a < b;
                    case 'gte':
                    case '>=': return a >= b;
                    case 'lte':
                    case '<=': return a <= b;
                    case 'in': return Array.isArray(b) && b.includes(a);
                    case 'nin': return Array.isArray(b) && !b.includes(a);
                    case 'includes': return typeof a === 'string' && a.includes(b);
                    case 'notIncludes': return typeof a === 'string' && !a.includes(b);
                    default: return false;
                }
            };

            let fulfilled = 0;

            for (let condition of field.conditions) {
                const operator = condition.operator || '==';
                const value = this.item[condition.property];

                if (compare(value, condition.value, operator)) {
                    fulfilled++;
                }
            }

            return fulfilled === field.conditions.length;
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
