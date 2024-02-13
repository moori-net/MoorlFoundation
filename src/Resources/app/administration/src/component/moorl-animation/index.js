const {Component} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('moorl-animation', {
    template,

    props: {
        value: {
            type: Array,
            required: false,
            default: [],
        },
    },

    data() {
        return {
            currentValue: null,
            snippetPrefix: 'moorl-animation.',
        };
    },

    watch: {
        value(value) {
            this.$emit('change', this.value);
        }
    },

    created() {
        this.currentValue = JSON.parse(JSON.stringify(this.value));
    },

    computed: {
        moorlFoundation() {
            return MoorlFoundation;
        }
    },

    methods: {
        addEntry() {
            this.value.push({
                cssSelector: '.cms-block',
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
            });

            this.$forceUpdate();
        },

        deleteEntry(index) {
            this.value.splice(index, 1);

            this.$forceUpdate();
        }
    }
});
