const {Criteria} = Shopware.Data;

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
            required: true
        },
        item: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            formStruct: null,
            isLoading: true,
        };
    },

    computed: {
        formBuilderHelper() {
            return new MoorlFoundation.FormBuilderHelper({
                entity: this.entity,
                componentName: this.componentName,
                item: this.item,
                tc: this.$tc,
            });
        },

        translationHelper() {
            return this.formBuilderHelper.translationHelper;
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
            this.item.taxId = taxes[0].id;
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
            return field.attributes;
        },

        isVisible(field) {
            return MoorlFoundation.ConditionHelper.isVisible(field, this.item);
        },

        isDisabled(field) {
            return !this.isVisible(field) || field.attributes?.disabled;
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
