import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-behaviour-card', {
    template,

    props: {
        item: {
            type: Object,
            required: true,
        },
    },

    created() {
        this.item.behaviour ??= [
            {visible: true, order: 2, width: 12, label: 'base'},
            {visible: true, order: 3, width: 12, label: 'sm'},
            {visible: true, order: 3, width: 12, label: 'md'},
            {visible: true, order: 4, width: 12, label: 'lg'},
            {visible: true, order: 6, width: 6, label: 'xl'}
        ];
    }
});
