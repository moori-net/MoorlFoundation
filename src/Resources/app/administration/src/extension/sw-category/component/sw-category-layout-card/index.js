const {Component, Mixin} = Shopware;

import template from './sw-category-layout-card.html.twig';

Component.override('sw-category-layout-card', {
    template,

    inject: [
        'foundationApiService'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            showLayoutAdoptChildrenModal: false,
            showLayoutAdoptSiblingsModal: false,
        };
    },

    methods: {
        onLayoutAdoptChildren() {
            this.foundationApiService.get(`/moorl-foundation/admin-helper/layout-adopt-children/${this.category.id}`).then((response) => {
                if (response?.errors) {
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: response.errors[0].message,
                    });
                } else {
                    this.createNotificationSuccess({
                        title: this.$tc('global.default.success'),
                        message: this.$tc('sw-category.component.sw-category-layout-card.success', 0, response),
                    });
                }

                this.showLayoutAdoptChildrenModal = false;
            }).catch((exception) => {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail,
                });

                this.showLayoutAdoptChildrenModal = false;
            });
        },

        onLayoutAdoptSiblings() {
            this.foundationApiService.get(`/moorl-foundation/admin-helper/layout-adopt-siblings/${this.category.id}`).then((response) => {
                if (response?.errors) {
                    this.createNotificationError({
                        title: this.$tc('global.default.error'),
                        message: response.errors[0].message,
                    });
                } else {
                    this.createNotificationSuccess({
                        title: this.$tc('global.default.success'),
                        message: this.$tc('sw-category.component.sw-category-layout-card.success', 0, response),
                    });
                }

                this.showLayoutAdoptChildrenModal = false;
            }).catch((exception) => {
                const errorDetail = Shopware.Utils.get(exception, 'response.data.errors[0].detail');
                this.createNotificationError({
                    title: this.$tc('global.default.error'),
                    message: errorDetail,
                });

                this.showLayoutAdoptChildrenModal = false;
            });
        }
    },
});
