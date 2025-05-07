const {Criteria} = Shopware.Data;
const {get, set} = Shopware.Utils.object;

Shopware.Mixin.register('moorl-form', {
    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder')
    ],

    inject: [
        'customFieldDataProviderService',
        'repositoryFactory'
    ],

    props: {
        entity: {
            type: String,
            required: false,
            default: undefined
        },
        componentName: {
            type: String,
            required: true,
            default: 'moorl-form'
        },
        item: {
            type: Object,
            required: true,
        },
        pathAppend: {
            type: String,
            required: false,
            default: undefined
        },
        mapping: {
            type: Object,
            required: false,
            default: undefined
        },
        hideDisabledFields: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    data() {
        return {
            formStruct: null,
            isLoading: true,
        };
    },

    computed: {
        masterMapping() {
            return this.mapping;
        },

        fieldModels() {
            return new Proxy({}, {
                get: (_, prop) => {
                    const path = String(this.pathAppend ? `${prop}.${this.pathAppend}` : prop);
                    if (get(this.item.extensions, path) !== undefined) {
                        return get(this.item.extensions, path);
                    }
                    return get(this.item, path);
                },

                set: (_, prop, value) => {
                    const path = String(this.pathAppend ? `${prop}.${this.pathAppend}` : prop);
                    if (get(this.item.extensions, path) !== undefined) {
                        set(this.item.extensions, path, value);
                    } else {
                        set(this.item, path, value);
                    }
                    return true;
                }
            });
        },

        translationHelper() {
            return new MoorlFoundation.TranslationHelper({
                $tc: this.$tc,
                componentName: this.componentName,
            });
        },

        formBuilderHelper() {
            return new MoorlFoundation.FormBuilderHelper({
                entity: this.entity,
                componentName: this.componentName,
                item: this.item,
                translationHelper: this.translationHelper,
                masterMapping: this.masterMapping
            });
        },

        defaultTab() {
            return this.formStruct?.tabs?.[0]?.id ?? null;
        },

        taxRepository() {
            return this.repositoryFactory.create('tax');
        },

        currencyRepository() {
            return this.repositoryFactory.create('currency');
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
        async createdComponent() {
            await this.loadCustomData();
            await this.loadCurrency();
            await this.loadTax();
            await this.loadCustomFieldSets();

            this.formStruct = await this.formBuilderHelper.buildFormStruct();

            this.isLoading = false;
        },

        async loadCustomData() {
            return Promise.resolve();
        },

        async loadTax() {
            const criteria = new Criteria();

            if (this.item.taxId) {
                criteria.setIds([this.item.taxId]);
            }

            const taxes = await this.taxRepository.search(criteria);

            this.formBuilderHelper.tax = taxes[0];
        },

        async loadCurrency() {
            const currencies = await this.currencyRepository.search(new Criteria(1, 500));

            this.formBuilderHelper.currency = currencies.find((currency) => currency.isSystemDefault);
        },

        async loadCustomFieldSets() {
            if (this.item.customFields === undefined) {
                return Promise.resolve();
            }

            this.formBuilderHelper.customFieldSets = await this.customFieldDataProviderService.getCustomFieldSets(this.entity);
        },

        fieldAttributes(field) {
            return {
                ...field.attributes,
                disabled: this.isDisabled(field)
            };
        },

        isVisibleComponent(field) {
            return this.hideDisabledFields ? this.isVisible(field) : true
        },

        isVisible(field) {
            return MoorlFoundation.ConditionHelper.isVisible(field, this.item);
        },

        isDisabled(field) {
            return !this.isVisible(field) || field.attributes?.disabled;
        },

        getError(field) {
            if (!field.attributes?.required) {
                return undefined;
            }

            const isEntity = this.item && typeof this.item.getEntityName === 'function';
            if (!isEntity) {
                return undefined;
            }

            return Shopware.Store.get('error').getApiError(this.item, field.name);
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
                'grid-column': `span ${field.cols}`
            }
        }
    }
});
