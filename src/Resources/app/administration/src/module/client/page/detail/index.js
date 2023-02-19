import template from './index.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
const utils = Shopware.Utils;

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
        getOptions() {
            this.isLoading = true;

            this.foundationApiService.get(`/moorl-foundation/settings/client/options`).then(response => {
                this.options = response;
                this.isLoading = false;
            }).catch((exception) => {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: exception,
                });
                this.isLoading = false;
            });
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
