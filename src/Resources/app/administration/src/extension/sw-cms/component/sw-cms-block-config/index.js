import template from './sw-cms-block-config.html.twig';

const {Component} = Shopware;

Component.override('sw-cms-block-config', {
    template,

    created() {
        if (!this.block.customFields) {
            this.$set(this.block, 'customFields', {});
        }

        if (!this.block.customFields.moorl_block_behaviour) {
            this.$set(this.block.customFields, 'moorl_block_behaviour', {
                'xs': {
                    'inherit': true,
                    'show': true,
                    'width': 12,
                    'order': 0
                },
                'sm': {
                    'inherit': true,
                    'show': true,
                    'width': 12,
                    'order': 0
                },
                'md': {
                    'inherit': true,
                    'show': true,
                    'width': 12,
                    'order': 0
                },
                'lg': {
                    'inherit': true,
                    'show': true,
                    'width': 12,
                    'order': 0
                },
                'xl': {
                    'inherit': true,
                    'show': true,
                    'width': 12,
                    'order': 0
                }
            });
        }
    },

    methods: {
        onChange(value) {
            this.$set(this.block.customFields, 'moorl_block_behaviour', value);
        }
    }
});
