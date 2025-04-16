import template from './index.html.twig';

Shopware.Component.override('sw-cms-el-config-image-slider', {
    template,

    created() {
        this.createdComponentExtra();
    },

    methods: {
        async createdComponentExtra() {
            const extraConfig = {
                enableFeature: {
                    source: 'static',
                    value: true,
                },
                speed: {
                    source: 'static',
                    value: 2000,
                },
                autoplayTimeout: {
                    source: 'static',
                    value: 6000,
                },
                autoplay: {
                    source: 'static',
                    value: true,
                },
                autoplayHoverPause: {
                    source: 'static',
                    value: true,
                },
                animateIn: {
                    source: 'static',
                    value: null,
                },
                animateOut: {
                    source: 'static',
                    value: null,
                },
            };

            this.element.config = Object.assign(
                extraConfig,
                this.element.config
            );
        },
    },
});
