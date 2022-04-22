const {Component, Mixin} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('moorl-animation-v2', {
    template,

    props: {
        value: {
            type: Object,
            required: true,
            default: {}
        }
    },

    data() {
        return {
            snippetPrefix: 'moorl-element-animation.',
        };
    },

    computed: {
        moorlFoundation() {
            return MoorlFoundation;
        },

        defaultAnimation() {
            return {
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
            };
        },
    },

    created() {
        this.initValue();
    },

    methods: {
        initValue() {
            for (const [key, animation] of Object.entries(this.defaultAnimation)) {
                if (!this.value[key]) {
                    this.$set(this.value, key, animation);
                }
            }
        }
    }
});
