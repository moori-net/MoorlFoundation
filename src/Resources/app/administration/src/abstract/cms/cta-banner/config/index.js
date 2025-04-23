import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-cta-banner-config', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    data() {
        return {
            defaultConfig: null,
            isLoading: true,
        };
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            await this.initElementConfig(this.element.type);
            await this.initElementData(this.element.type);

            this.defaultConfig = this.cmsElements[this.element.type].defaultConfig;

            this.isLoading = false;
        }
    },
});
