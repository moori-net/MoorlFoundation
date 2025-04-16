import template from './index.html.twig';

Shopware.Component.register('moorl-element-animation', {
    template,

    props: {
        element: {
            type: Object,
            required: true,
        },
    },

    created() {
        this.element.config = Object.assign(
            {
                moorlAnimation: {
                    source: 'static',
                    value: null,
                },
            },
            this.element.config
        );
    },
});
