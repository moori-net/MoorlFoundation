import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-bs-config', {
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
        name: {type: String, required: false, default: null},
    },

    computed: {
        defaultBehaviour() {
            return [
                {
                    'icon': 'regular-mobile',
                    'breakpoint': 'xs'
                },
                {
                    'icon': 'regular-mobile',
                    'breakpoint': 'sm'
                },
                {
                    'icon': 'regular-tablet',
                    'breakpoint': 'md'
                },
                {
                    'icon': 'regular-tablet',
                    'breakpoint': 'lg'
                },
                {
                    'icon': 'regular-desktop',
                    'breakpoint': 'xl'
                },
                {
                    'icon': 'regular-desktop',
                    'breakpoint': 'xxl'
                },
            ];
        },

        elementName() {
            if (this.name) {
                return this.name;
            }
            return this.$tc('moorl-bs-config.label.elementName');
        }
    },

    created() {
        this.initValue();
    },

    methods: {
        initValue() {
            for (let behaviour of this.defaultBehaviour) {
                if (!this.value[behaviour.breakpoint]) {
                    this.value[behaviour.breakpoint] = {
                        'inherit': true,
                        'show': true,
                        'width': 12,
                        'order': 0
                    };
                }
            }
        },

        helpText(breakpoint, property) {
            return {
                position: 'top',
                showDelay: 1000,
                hideDelay: 0,
                message: this.$tc(`moorl-bs-config.helpText.${property}`, 0, {
                    breakpoint,
                    elementName: this.elementName,
                })
            }
        }
    }
});
