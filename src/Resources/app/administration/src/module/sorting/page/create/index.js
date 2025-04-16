import template from '../detail/index.html.twig';

Shopware.Component.extend('moorl-sorting-create', 'moorl-sorting-detail', {
    template,
    methods: {
        getItem() {
            this.item = this.repository.create(Shopware.Context.api);
            this.item.fields = [];
            this.item.priority = 0;
            this.isLoading = false;
        },

        onClickSave() {
            this.isLoading = true;

            this.repository
                .save(this.item, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({
                        name: 'moorl.sorting.detail',
                        params: { id: this.item.id },
                    });
                })
                .catch((exception) => {
                    this.isLoading = false;
                    if (
                        exception.response.data &&
                        exception.response.data.errors
                    ) {
                        exception.response.data.errors.forEach((error) => {
                            this.createNotificationWarning({
                                title: this.$tc(
                                    'moorl-foundation.notification.errorTitle'
                                ),
                                message: error.detail,
                            });
                        });
                    }
                });
        },
    },
});
