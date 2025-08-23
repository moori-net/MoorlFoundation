import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-collapse', {
    template,

    props: {
        label: {
            type: String,
            required: false,
            default: 'Collapse Label',
        },
        expandOnLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            expanded: this.expandOnLoading,
        };
    },

    methods: {
        collapseItem() {
            this.expanded = !this.expanded;
        },
    },
});
