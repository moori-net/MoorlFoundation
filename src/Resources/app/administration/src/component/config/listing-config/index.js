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
        const fetched = MoorlFoundation.CmsElementHelper.fetchCmsElement('listing', defaultValue);

        if (!this.value) {
            this.$emit('update:value', fetched.defaultConfig);
        }

        this.cmsElementMapping = fetched.cmsElementMapping;

        this.isLoading = false;
    },
});
