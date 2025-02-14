const {Component} = Shopware;

import template from './index.html.twig';

Component.register('moorl-svg-shape', {
    template,

    emits: [
        'update:value'
    ],

    props: {
        value: {
            type: Object,
            required: true,
            default: {}
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            const value = Object.assign({
                shape: 'none',
                unit: 'px',
                width: 100,
                height: 100,
                x: 0,
                y: 0,
                rx: 0,
                ry: 0,
                r: 50,
                cx: 50,
                cy: 50,
                style: ""
            });


            Object.assign(value, this.value);

            this.$emit('update:value', value);
        }
    }
});
