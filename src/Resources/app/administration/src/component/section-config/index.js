const {Component} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('moorl-section-config', {
    template,

    props: {
        section: {
            type: Object,
            required: true
        },
    },

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            show: false,
        };
    },

    computed: {
        iconColor() {
            if (this.section?.customFields?.moorl_section_config?.salesChannel?.length) {
                return "#FF0000";
            }
            if (this.section?.customFields?.moorl_section_config?.customerGroup?.length) {
                return "#00FF00";
            }
            return null;
        },

        salesChannelRepository() {
            return this.repositoryFactory.create('sales_channel');
        },

        customerGroupRepository() {
            return this.repositoryFactory.create('customer_group');
        },

        separatorOptions() {
            return [
                {value: 'scratch', label: 'scratch'},
                {value: 'toothed', label: 'toothed'},
                {value: 'waves', label: 'waves'},
                {value: 'papercut', label: 'papercut'},
            ];
        },

        offsetOptions() {
            return [
                {value: '50', label: '50'},
                {value: '100', label: '100'},
                {value: '150', label: '150'},
                {value: '200', label: '200'},
            ];
        },

        colorOptions() {
            return [
                {value: 'primary', label: 'primary'},
                {value: 'primary-dark', label: 'primary-dark'},
                {value: 'primary-light', label: 'primary-light'},
                {value: 'secondary', label: 'secondary'},
                {value: 'light', label: 'light'},
                {value: 'dark', label: 'dark'},
                {value: 'text-color', label: 'text-color'},
                {value: 'background-color', label: 'background-color'},
                {value: 'primary-top-secondary-bottom', label: 'primary-top-secondary-bottom'},
                {value: 'primary-top-dark-bottom', label: 'primary-top-dark-bottom'},
                {value: 'primary-top-light-bottom', label: 'primary-top-light-bottom'},
                {value: 'primary-top-background-bottom', label: 'primary-top-background-bottom'},
                {value: 'primary-top-text-bottom', label: 'primary-top-text-bottom'},
                {value: 'primary-top-indigo-bottom', label: 'primary-top-indigo-bottom'},
                {value: 'indigo-top-primary-bottom', label: 'indigo-top-primary-bottom'},
                {value: 'light-top-primary-bottom', label: 'light-top-primary-bottom'},
                {value: 'dark-top-primary-bottom', label: 'dark-top-primary-bottom'},
                {value: 'background-top-text-bottom', label: 'background-top-text-bottom'},
                {value: 'background-top-primary-bottom', label: 'background-top-primary-bottom'}
            ];
        },

        filterOptions() {
            return [
                {value: 'grayscale', label: 'grayscale'},
                {value: 'blur', label: 'blur'},
            ];
        },

        paintOptions() {
            return [
                {value: 'dots', label: 'dots'},
                {value: 'generateddots', label: 'generateddots'},
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
                this.section.customFields = {};
            }
            if (!this.section.customFields.moorl_section_config) {
                this.section.customFields.moorl_section_config = {
                    color: null
                };
            }
            if (Array.isArray(this.section.customFields.moorl_section_config)) {
                this.section.customFields.moorl_section_config = {
                    color: null
                };
            }
        }
    }
});
