import template from './index.html.twig';
import defaultValue from './default.json';

Shopware.Component.register('moorl-listing-config', {
    template,

    emits: ['update:value'],

    props: {
        value: {
            type: Object,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            isLoading: true,
            cmsElementMapping: null,
        };
    },

    created() {
        const prepared = MoorlFoundation.CmsElementHelper.prepareCmsElementMapping('listing', defaultValue);

        if (!this.value) {
            this.$emit('update:value', prepared.defaultConfig);
        }

        this.cmsElementMapping = prepared.cmsElementMapping;

        this.isLoading = false;
    },
});
