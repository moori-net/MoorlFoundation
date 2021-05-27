const {Component, Mixin} = Shopware;

import template from './index.html.twig';

Component.register('moorl-foundation-settings-demo-data', {
    template,

    inject: [
        'repositoryFactory',
        'foundationApiService'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            item: {
                salesChannelId: null,
                pluginName: null,
                name: null,
            },
            optionIndex: 0,
            options: null,
            isLoading: true,
            processSuccess: false
        };
    },

    created() {
        this.getOptions();
    },

    methods: {
        selectOption(index) {
            if (typeof index == 'undefined') {
                index = this.optionIndex;
            }

            this.item.pluginName = this.options[index].pluginName;
            this.item.name = this.options[index].name;
            this.item.type = this.options[index].type;
        },

        getOptions() {
            this.isLoading = true;

            this.foundationApiService.get(`/moorl-foundation/settings/demo-data/options`).then(response => {
                this.options = response;
                this.isLoading = false;

                if (response.length === 0) {
                    return;
                }

                this.selectOption(0);
            }).catch((exception) => {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: exception,
                });

                this.isLoading = false;
            });
        },

        install() {
            this.isLoading = true;

            this.foundationApiService.post(`/moorl-foundation/settings/demo-data/install`, this.item).then(response => {
                this.createNotificationSuccess({
                    message: this.$tc('moorl-foundation.notification.demoDataInstalled')
                });

                this.isLoading = false;
            }).catch((exception) => {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: exception,
                });

                this.isLoading = false;
            });
        },

        remove() {
            this.isLoading = true;

            this.foundationApiService.post(`/moorl-foundation/settings/demo-data/remove`, this.item).then(response => {
                this.createNotificationSuccess({
                    message: this.$tc('moorl-foundation.notification.demoDataRemoved')
                });

                this.isLoading = false;
            }).catch((exception) => {
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: exception,
                });

                this.isLoading = false;
            });
        }
    }
});
