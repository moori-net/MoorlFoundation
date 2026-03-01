import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-base', {
    template,

    props: {
        element: {
            type: Object,
            required: true,
        },
        elementData: {
            type: Object,
            required: true,
        },
    }
});
