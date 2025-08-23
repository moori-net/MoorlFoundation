import template from './index.html.twig';

Shopware.Component.override('moorl-client-detail', {
    template,

    inject: ['foundationApiService'],

    data() {
        return {
            entity: 'moorl_client',
            options: [],
        };
    },

    created() {
        this.getOptions();
    },

    methods: {
        async onClickTest() {
            this.isLoading = true;

            this.foundationApiService
                .get(`/moorl-foundation/settings/client/test/${this.item.id}`)
                .then((response) => {
                    if (response?.errors) {
                        this.createNotificationError({
                            title: this.$tc('global.default.error'),
                            message: response.errors[0].message,
                        });
                    } else {
                        this.createNotificationSuccess({
                            title: this.$tc('global.default.success'),
                            message: this.$tc('global.default.success'),
                        });

                        if (response.url) {
                            window.open(response.url, '_blank');
                        }
                    }

                    this.isLoading = false;
                })
                .catch((exception) => {
                    const errorDetail = Shopware.Utils.get(
                        exception,
                        'response.data.errors[0].detail'
                    );
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: errorDetail,
                    });

                    this.isLoading = false;
                });
        },

        getOptions() {
            this.isLoading = true;

            this.foundationApiService
                .get(`/moorl-foundation/settings/client/options`)
                .then((response) => {
                    this.options = response;
                    this.isLoading = false;
                })
                .catch((exception) => {
                    const errorDetail = Shopware.Utils.get(
                        exception,
                        'response.data.errors[0].detail'
                    );
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
        }
    }
});
