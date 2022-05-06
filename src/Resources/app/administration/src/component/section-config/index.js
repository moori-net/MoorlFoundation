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

    data() {
        return {
            snippetPrefix: 'moorl-section-config.',
            show: false,
        };
    },

    computed: {
        separatorOptions() {
            return [
                'none',
                'scratch',
                'toothed',
                'waves',
                'papercut'
            ];
        },

        offsetOptions() {
            return [
                'none',
                '50',
                '100',
                '150',
                '200'
            ];
        },

        colorOptions() {
            return [
                'none',
                'primary',
                'primary-dark',
                'primary-light',
                'secondary',
                'light',
                'dark',
                'text-color',
                'background-color',
                'primary-top-secondary-bottom',
                'primary-top-dark-bottom',
                'primary-top-light-bottom',
                'primary-top-background-bottom',
                'primary-top-text-bottom',
                'primary-top-indigo-bottom',
                'indigo-top-primary-bottom',
                'light-top-primary-bottom',
                'dark-top-primary-bottom',
                'background-top-text-bottom',
                'background-top-primary-bottom'
            ];
        },

        filterOptions() {
            return [
                'none',
                'grayscale',
                'blur'
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
            if (!this.section.customFields.moorl_section_grid_config) {
                this.$set(this.section.customFields, 'moorl_section_config', {});
            }
        }
    }
});
