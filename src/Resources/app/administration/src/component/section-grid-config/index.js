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

    created() {
        this.initCustomFields();
    },

    methods: {
        initCustomFields() {
            if (!this.section) {
                return;
            }
            if (!this.section.customFields) {
                this.section.customFields = {};
            }
            if (!this.section.customFields.moorl_section_column_count) {
                this.section.customFields.moorl_section_column_count = 3;
            }
            if (!Number.isInteger(this.section.customFields.moorl_section_spacing)) {
                this.section.customFields.moorl_section_spacing = 30;
            }
            if (!this.section.customFields.moorl_section_grid_config) {
                this.section.customFields.moorl_section_grid_config = [];
            }
            this.section.customFields.moorl_section_grid_config.length
                = this.section.customFields.moorl_section_column_count;
            for (let i = 0; i < this.section.customFields.moorl_section_column_count; i++) {
                if (!this.section.customFields.moorl_section_grid_config[i]) {
                    this.section.customFields.moorl_section_grid_config[i] = {
                        alignItems: 'normal',
                        justifyContent: 'normal',
                        isSidebar: false,
                        isSticky: false,
                        offsetTop: '30px',
                        value: {
                            lg: {
                                show: true,
                                order: 0,
                                width: 3,
                                inherit: false
                            },
                            md: {
                                show: true,
                                order: 0,
                                width: 12,
                                inherit: true
                            },
                            sm: {
                                show: true,
                                order: 0,
                                width: 12,
                                inherit: true
                            },
                            xl: {
                                show: true,
                                order: 0,
                                width: 3,
                                inherit: false
                            },
                            xs: {
                                show: true,
                                order: 0,
                                width: 12,
                                inherit: true
                            }
                        },
                    };
                }
            }
        }
    }
});
