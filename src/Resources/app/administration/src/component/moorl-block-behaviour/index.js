const {Component} = Shopware;

import template from './index.html.twig';
import './index.scss';

Component.register('moorl-block-behaviour', {
    template,

    props: {
        value: {
            type: Object,
            required: true
        },
    },

    data() {
        return {
            snippetPrefix: 'moorl-block-behaviour.'
        };
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
    }
});
