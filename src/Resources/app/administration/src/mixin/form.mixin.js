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
            default: {}
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
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        productSearchContext() {
            const context = Object.assign({}, Shopware.Context.api);
            context.inheritance = true;

            return context;
        },
    },

    watch: {
        item: {
            handler() {
                this.formStruct = this.formBuilderHelper.buildFormStruct();
            },
            deep: false
        },
        'item.productId': {
            handler() {this.loadTax().then(() => {this.reloadFields();});},
            deep: false
        },
        'item.taxId': {
            handler() {this.loadTax().then(() => {this.reloadFields();});},
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
            this.reloadFields();
        },

        async loadCustomData() {
            return Promise.resolve();
        },

        async loadTax() {
            if (!this.itemPropertyExists('taxId')) {
                return;
            }

            const criteria = new Criteria();
            if (this.itemPropertyExists('productId') && this.item.productId) {
                criteria.addAssociation('tax');
                criteria.setIds([this.item.productId]);
                const products = await this.productRepository.search(criteria, this.productSearchContext);
                this.item.taxId = products[0].taxId;
                this.formBuilderHelper.tax = products[0].tax;
            } else {
                criteria.addSorting(Criteria.sort('position', 'ASC', false));
                if (this.item.taxId) {
                    criteria.setIds([this.item.taxId]);
                }
                const taxes = await this.taxRepository.search(criteria);
                this.item.taxId = taxes[0].id;
                this.formBuilderHelper.tax = taxes[0];
            }
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

        itemPropertyExists(prop) {
            if (!this.entity) {
                return false;
            }
            const fields = Shopware.EntityDefinition.get(this.entity).properties;
            return fields.hasOwnProperty(prop);
        },

        reloadFields() {
            this.overrideFieldAttributes('moorl-price-field', {tax: this.formBuilderHelper.tax});
        },

        fieldAttributes(field) {
            return {
                ...field.attributes,
                disabled: this.isDisabled(field)
            };
        },

        overrideFieldAttributes(name, attributes) {
            this.formStruct.tabs.forEach((tab) => {
                tab.cards.forEach((card) => {
                    card.fields.forEach((field) => {
                        if (field.name !== name && field.componentName !== name) {
                            return;
                        }
                        field.attributes = {
                            ...field.attributes,
                            ...attributes
                        };
                    });
                });
            });
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
