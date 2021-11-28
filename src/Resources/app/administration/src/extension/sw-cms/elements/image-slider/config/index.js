const {Component} = Shopware;

import template from './index.html.twig';

Component.override('sw-cms-el-config-image-slider', {
    template,

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.image-slider.',
        };
    },

    created() {
        this.createdComponentExtra();
    },

    computed: {
        moorlFoundation() {
            return MoorlFoundation;
        }
    },

    methods: {
        async createdComponentExtra() {
            const extraConfig = {
                enableFeature: {
                    source: 'static',
                    value: true
                },
                speed: {
                    source: 'static',
                    value: 2000
                },
                autoplayTimeout: {
                    source: 'static',
                    value: 6000
                },
                autoplay: {
                    source: 'static',
                    value: true
                },
                autoplayHoverPause: {
                    source: 'static',
                    value: true
                },
                animateIn: {
                    source: 'static',
                    value: null
                },
                animateOut: {
                    source: 'static',
                    value: null
                }
            };

            this.element.config = Object.assign(extraConfig, this.element.config);
        }
    }
});
