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
        },

        defaultEntry() {
            return {
                cssSelector: '.cms-block',
                animateInActive: false,
                animateIn: null,
                animateInSpeed: 1000,
                animateInTimeout: 'static',
                animateInRule: 'isInViewport',
                animateOutActive: false,
                animateOut: null,
                animateOutSpeed: 1000,
                animateOutTimeout: 0,
                animateOutRule: 'isInViewport',
                animateHoverActive: false,
                animateHover: null,
                animateHoverSpeed: 1000,
                animateHoverTimeout: 0,
                animateHoverRule: 'isInViewport',
            };
        }
    },

    methods: {
        addEntry() {
            this.currentValue.push(this.defaultEntry);
            this.value.push(this.defaultEntry);
        },

        deleteEntry(index) {
            this.currentValue.splice(index, 1);
            this.value.splice(index, 1);
        },

        emitChange() {
            this.$emit('change', this.currentValue);
        }
    }
});
