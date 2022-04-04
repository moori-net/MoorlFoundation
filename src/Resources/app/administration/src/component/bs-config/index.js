const {Component} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('moorl-bs-config', {
    template,

    props: {
        value: {
            type: Object,
            required: true,
            default: {}
        },
        showInherit: {type: Boolean, required: false, default: true},
        showShow: {type: Boolean, required: false, default: true},
        showWidth: {type: Boolean, required: false, default: true},
        showOrder: {type: Boolean, required: false, default: true},
        label: {type: String, required: false, default: null},
    },

    computed: {
        defaultBehaviour() {
            return [
                {
                    'icon': 'default-device-mobile',
                    'breakpoint': 'xs'
                },
                {
                    'icon': 'default-device-mobile',
                    'breakpoint': 'sm'
                },
                {
                    'icon': 'default-device-tablet',
                    'breakpoint': 'md'
                },
                {
                    'icon': 'default-device-tablet',
                    'breakpoint': 'lg'
                },
                {
                    'icon': 'default-device-desktop',
                    'breakpoint': 'xl'
                },
            ];
        }
    },

    created() {
        this.initValue();
    },

    methods: {
        initValue() {
            for (let behaviour of this.defaultBehaviour) {
                if (!this.value[behaviour.breakpoint]) {
                    this.$set(this.value, behaviour.breakpoint, {
                        'inherit': true,
                        'show': true,
                        'width': 12,
                        'order': 0
                    });
                }
            }
        }
    }
});
