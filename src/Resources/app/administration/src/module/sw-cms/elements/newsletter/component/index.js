import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-newsletter', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    computed: {
        newsletterButtonCss() {
            return {
                color: this.element.config.buttonTextColor.value,
                'background-color': this.element.config.buttonBackground.value,
            };
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-newsletter');
            this.initElementData('moorl-newsletter');
        },
    },
});
