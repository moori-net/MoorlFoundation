import template from './index.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('moorl-sorting-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],

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
            isLoading: false,
            processSuccess: false
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('moorl_sorting');
        },

        defaultCriteria() {
            return new Criteria();
        },

        identifier() {
            return this.placeholder(this.item, 'label');
        }
    },

    created() {
        this.getItem();
    },

    methods: {
        getItem() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.defaultCriteria)
                .then((entity) => {
                    this.item = entity;
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
