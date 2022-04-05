import template from './index.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('moorl-sorting-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],

    shortcuts: {
        'SYSTEMKEY+m': 'openModal',
    },

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    data() {
        return {
            item: null,
            isLoading: true,
            processSuccess: false,
            productSortingEntity: null,
            toBeDeletedCriteria: null
        };
    },

    computed: {
        openModal() {
            alert("test");
        },

        repository() {
            return this.repositoryFactory.create('moorl_sorting');
        },

        defaultCriteria() {
            return new Criteria();
        },

        entityOptions() {
            const storeOptions = [];
            const definitionRegistry = Shopware.EntityDefinition.getDefinitionRegistry();

            definitionRegistry.forEach(function (value, key, map) {
                storeOptions.push({
                    name: `${key}`
                });
            });

            return storeOptions;
        },

        identifier() {
            return this.placeholder(this.item, 'label');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getItem()
        },

        saveProductSorting() {
            return this.repository.save(this.item);
        },

        onSave() {
            return this.saveProductSorting()
                .then(() => {
                    const sortingOptionName = this.item.label;

                    this.createNotificationSuccess({
                        message: this.$t('sw-settings-listing.base.notification.saveSuccess', { sortingOptionName }),
                    });
                })
                .catch(() => {
                    const sortingOptionName = this.item.label;

                    this.createNotificationError({
                        message: this.$t('sw-settings-listing.base.notification.saveError', { sortingOptionName }),
                    });
                });
        },

        getCriteriaTemplate(fieldName) {
            return { field: fieldName, order: 'asc', priority: 1, naturalSorting: 0 };
        },

        onDeleteCriteria(toBeRemovedItem) {
            this.toBeDeletedCriteria = toBeRemovedItem;
        },

        onConfirmDeleteCriteria() {
            this.item.fields = this.item.fields.filter(currentCriteria => {
                return currentCriteria.field !== this.toBeDeletedCriteria.field;
            });
            this.saveProductSorting();
            this.toBeDeletedCriteria = null;
        },

        onAddCriteria(fieldName) {
            if (!fieldName) {
                return;
            }

            const newCriteria = this.getCriteriaTemplate(fieldName);

            if (!this.item.fields) {
                this.item.fields = [];
            }

            this.item.fields.push(newCriteria);
        },

        onCancelEditCriteria(item) {
            if (this.getProductSortingEntityId()) {
                this.fetchProductSortingEntity();

                return;
            }

            this.item.fields = this.item.fields.filter(currentCriteria => {
                return currentCriteria.field !== item.field;
            });
        },

        getItem() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.defaultCriteria)
                .then((response) => {
                    if (!Array.isArray(response.fields)) {
                        response.fields = [];
                    }

                    this.item = response;

                    this.isLoading = false;
                });
        },

        onChangeLanguage() {
            this.getItem();
        },

        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                    this.processSuccess = true;
                }).catch((exception) => {
                this.isLoading = false;
                if (exception.response.data && exception.response.data.errors) {
                    exception.response.data.errors.forEach((error) => {
                        this.createNotificationWarning({
                            title: this.$tc('moorl-foundation.notification.errorTitle'),
                            message: error.detail
                        });
                    });
                }
            });
        },

        saveFinish() {
            this.processSuccess = false;
        }
    }
});
