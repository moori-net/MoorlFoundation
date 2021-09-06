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
                animateIn: {
                    source: 'static',
                    value: null
                },
                animateInSpeed: {
                    source: 'static',
                    value: 1000
                },
                animateInTimeout: {
                    source: 'static',
                    value: 0
                },
                animateInRule: {
                    source: 'static',
                    value: 'isInViewport'
                },
                animateOut: {
                    source: 'static',
                    value: null
                },
                animateOutSpeed: {
                    source: 'static',
                    value: 1000
                },
                animateOutTimeout: {
                    source: 'static',
                    value: 0
                },
                animateOutRule: {
                    source: 'static',
                    value: 'isInViewport'
                },
                animateHover: {
                    source: 'static',
                    value: null
                },
                animateHoverSpeed: {
                    source: 'static',
                    value: 1000
                },
                animateHoverTimeout: {
                    source: 'static',
                    value: 0
                },
                animateHoverRule: {
                    source: 'static',
                    value: 'isInViewport'
                },
            };

            this.element.config = Object.assign(extraConfig, this.element.config);
        }
    }
});
