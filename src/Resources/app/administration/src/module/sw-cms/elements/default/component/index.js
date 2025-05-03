import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-default', {
    template,

    props: {
        element: {
            type: Object,
            required: true,
        },
    }
});
