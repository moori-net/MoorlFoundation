import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-base-preview', {
    template,

    props: {
        element: {
            type: Object,
            required: true,
        },
        plugin: {
            type: Object,
            required: true,
        },
    }
});
