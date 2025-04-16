import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-block-behaviour', {
    template,

    props: {
        block: {
            type: Object,
            required: true
        },
    },

    computed: {
        isInitialized() {
            if (!this.block) {
                return false;
            }
            if (!this.block?.customFields?.moorl_block_behaviour) {
                return false;
            }
            return true;
        }
    },

    watch: {
        block() {
            this.initValue();
        }
    },

    created() {
        this.initValue();
    },

    methods: {
        initValue() {
            if (!this.block.customFields) {
                this.block.customFields = {};
            }

            if (!this.block.customFields.moorl_block_behaviour) {
                this.block.customFields.moorl_block_behaviour = {
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
                };
            }
        }
    }
});
