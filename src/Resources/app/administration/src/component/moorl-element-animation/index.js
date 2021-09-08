const {Component, Mixin} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('moorl-element-animation', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            snippetPrefix: 'moorl-element-animation.',
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
                moorlAnimation: {
                    source: 'static',
                    value: {
                        cssSelector: null,
                        in: {
                            active: false,
                            name: 'none',
                            condition: 'isInViewport',
                            duration: 1000,
                            delay: 0,
                        },
                        out: {
                            active: false,
                            name: 'none',
                            condition: 'isInViewport',
                            duration: 1000,
                            delay: 0,
                        },
                        hover: {
                            active: false,
                            name: 'none',
                            condition: 'isInViewport',
                            duration: 1000,
                            delay: 0,
                        }
                    }
                }
            };

            this.element.config = Object.assign(extraConfig, this.element.config);
        }
    }
});
