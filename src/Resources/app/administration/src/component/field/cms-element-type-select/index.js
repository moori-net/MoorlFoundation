import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-cms-element-type-select', {
    template,

    inject: [
        'cmsService'
    ],

    computed: {
        cmsElements() {
            return this.cmsService.getCmsElementRegistry();
        },

        cmsTypeOptions() {
            const storeOptions = [];

            Object.entries(this.cmsElements).forEach(([key, value]) => {
                storeOptions.push({
                    label: this.$tc(value.label),
                    value: key,
                });
            });

            storeOptions.sort((a, b) => a.label.localeCompare(b.label));

            return storeOptions;
        },
    },
});
