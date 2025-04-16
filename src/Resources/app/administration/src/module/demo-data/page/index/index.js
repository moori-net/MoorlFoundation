import template from './index.html.twig';

Shopware.Component.register('moorl-demo-data-index', {
    template,

    inject: ['repositoryFactory', 'acl', 'foundationApiService'],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder'),
    ],

    data() {
        return {
            test: null,
            salesChannelId: null,
            confirmed: false,
            optionIndex: 0,
            options: [],
            isLoading: true,
            processSuccess: false,
        };
    },

    computed: {
        currentOption() {
            const option = this.options[this.optionIndex];

            option.salesChannelId = this.salesChannelId;

            return option;
        },
    },

    created() {
        this.getOptions();
    },

    methods: {
        getOptions() {
            this.isLoading = true;

            this.foundationApiService
                .get(`/moorl-foundation/settings/demo-data/options`)
                .then((response) => {
                    if (response.length === 0) {
                        return;
                    }

                    response.forEach((option) => {
                        let label;
                        if (option.type === 'data') {
                            label = `${this.$tc('moorl-foundation.label.baseData')} | ${option.pluginName}`;
                        } else {
                            label = `${this.$tc('moorl-foundation.label.standardDemo')} | ${option.pluginName} | ${option.name}`;
                        }

                        this.options.push({
                            label: label,
                            value: this.options.length,
                            pluginName: option.pluginName,
                            name: option.name,
                            type: option.type,
                        });
                    });

                    this.isLoading = false;
                })
                .catch((exception) => {
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: exception,
                    });

                    this.isLoading = false;
                });
        },

        install() {
            this.isLoading = true;

            this.foundationApiService
                .post(
                    `/moorl-foundation/settings/demo-data/install`,
                    this.currentOption
                )
                .then((response) => {
                    this.createNotificationSuccess({
                        message: this.$tc(
                            'moorl-foundation-settings-demo-data.installed'
                        ),
                    });

                    this.isLoading = false;
                })
                .catch((exception) => {
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: exception,
                    });

                    this.isLoading = false;
                });
        },

        remove() {
            this.isLoading = true;

            this.foundationApiService
                .post(
                    `/moorl-foundation/settings/demo-data/remove`,
                    this.currentOption
                )
                .then((response) => {
                    this.createNotificationSuccess({
                        message: this.$tc(
                            'moorl-foundation-settings-demo-data.removed'
                        ),
                    });

                    this.isLoading = false;
                })
                .catch((exception) => {
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: exception,
                    });

                    this.isLoading = false;
                });
        },
    },
});
