import template from './index.html.twig';

Shopware.Component.register('moorl-client-config-card', {
    template,

    inject: ['foundationApiService'],

    mixins: [Shopware.Mixin.getByName('notification')],

    props: {
        item: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            options: [],
            disabled: true
        };
    },

    watch: {
        'item.type': {
            handler(newValue, oldValue) {
                if (!newValue || newValue === oldValue) {
                    return;
                }
                this.loadConfig();
            },
            deep: false
        },
        'item.config': {
            handler() {
                this.disabled = true;
            },
            deep: true
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.loadOptions();
            this.disabled = this.item._isNew;
        },

        loadOptions() {
            this.foundationApiService
                .get(`/moorl-foundation/settings/client/options`)
                .then((response) => {
                    this.options = response;
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
                });
        },

        loadConfig() {
            this.item.config = {};
            this.disabled = true;

            this.options.forEach((option) => {
                if (this.item.type !== option.name) {
                    return;
                }

                option.configTemplate.forEach((config) => {
                    this.item.config[config.name] = config.default;
                });
            });
        },

        async onClickTest() {
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
                });
        },
    }
});
