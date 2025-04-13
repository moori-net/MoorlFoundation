const {Component, Mixin} = Shopware;

import template from './index.html.twig';

Component.register('moorl-element-animation', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponentExtra();
    },

    methods: {
        async createdComponentExtra() {
            const extraConfig = {
                moorlAnimation: {
                    source: 'static',
                    value: null
                }
            };

            this.element.config = Object.assign(extraConfig, this.element.config);
        }
    }
});
