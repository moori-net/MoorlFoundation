const {Component} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('moorl-section-grid-config', {
    template,

    props: {
        section: {
            type: Object,
            required: true
        },
    },

    computed: {
        flexAlignments() {
            return [
                {
                    name: 'normal',
                    id: 'normal'
                },
                {
                    name: 'flex-start',
                    id: 'flex-start'
                },
                {
                    name: 'center',
                    id: 'center'
                },
                {
                    name: 'flex-end',
                    id: 'flex-end'
                },
                {
                    name: 'space-around',
                    id: 'space-around'
                },
                {
                    name: 'space-between',
                    id: 'space-between'
                },
                {
                    name: 'space-evenly',
                    id: 'space-evenly'
                },
            ];
        }
    },

    created() {
        this.initCustomFields();
    },

    methods: {
        initCustomFields() {
            if (!this.section) {
                return;
            }
            if (!this.section.customFields) {
                this.$set(this.section, 'customFields', {});
            }
            if (!this.section.customFields.moorl_section_column_count) {
                this.$set(this.section.customFields, 'moorl_section_column_count', 3);
            }
            if (!this.section.customFields.moorl_section_grid_config) {
                this.$set(this.section.customFields, 'moorl_section_grid_config', []);
            }
            this.section.customFields.moorl_section_grid_config.length
                = this.section.customFields.moorl_section_column_count;
            for (let i = 0; i < this.section.customFields.moorl_section_column_count; i++) {
                if (!this.section.customFields.moorl_section_grid_config[i]) {
                    this.$set(this.section.customFields.moorl_section_grid_config, i, {
                        alignItems: 'normal',
                        justifyContent: 'normal',
                        isSidebar: false,
                        isSticky: false,
                        offsetTop: '30px',
                        value: {}
                    });
                }
            }
        }
    }
});
