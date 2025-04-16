import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-animation', {
    template,

    emits: ['update:value'],

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
        };
    },

    watch: {
        value(value) {
            this.$emit('change', this.value);
        },
    },

    created() {
        this.currentValue = JSON.parse(JSON.stringify(this.value));
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
                },
            });

            this.$emit('update:value', this.value);

            this.$forceUpdate();
        },

        deleteEntry(index) {
            this.value.splice(index, 1);

            this.$emit('update:value', this.value);

            this.$forceUpdate();
        },
    },
});
