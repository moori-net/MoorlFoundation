import template from './index.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('moorl-client-detail', {
    template,

    inject: [
        'repositoryFactory',
        'context',
        'foundationApiService'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            item: null,
            isLoading: false,
            processSuccess: false,
            options: [],
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('moorl_client');
        },

        defaultCriteria() {
            return new Criteria();
        }
    },

    created() {
        this.getOptions();
        this.getItem();
    },

    methods: {
        onClickTest() {
            this.isLoading = true;

            this.foundationApiService.get(`/moorl-foundation/settings/client/test/${this.item.id}`).then(() => {
                this.createNotificationSuccess({
                    title: this.$tc('global.default.success'),
                    message: this.$tc('global.default.success'),
                });
                this.isLoading = false;
            }).catch((exception) => {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail,
                });
                this.isLoading = false;
            });
        },

        getOptions() {
            this.isLoading = true;

            this.foundationApiService.get(`/moorl-foundation/settings/client/options`).then(response => {
                this.options = response;
                this.isLoading = false;
            }).catch((exception) => {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail,
                });
                this.isLoading = false;
            });
        },

        resetConfig() {
            this.item.config = {};

            for (let option of this.options) {
                if (option.name === this.item.type) {
                    for (let config of option.configTemplate) {
                        this.item.config[config.name] = config.default;
                    }
                }
            }
        },

        getItem() {
            this.repository
                .get(this.$route.params.id, Shopware.Context.api, this.defaultCriteria)
                .then((entity) => {
                    this.item = entity;
                });
        },

        onClickSave() {
            this.isLoading = true;
            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.getItem();
                    this.isLoading = false;
                    this.processSuccess = true;
                })
                .catch((exception) => {
                    this.isLoading = false;
                    this.createNotificationError({
                        title: this.$tc('moorl-foundation.notification.errorTitle'),
                        message: exception
                    });
                });
        },

        saveFinish() {
            this.processSuccess = false;
        },
    }
});
