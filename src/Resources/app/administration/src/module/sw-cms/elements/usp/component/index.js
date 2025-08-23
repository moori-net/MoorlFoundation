import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-usp', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    computed: {
        uspContainerCss() {
            if (this.element.config.alignment.value === 'center') {
                return {
                    'flex-direction': 'column',
                    'text-align': 'center',
                };
            }
        },

        uspIconCss() {
            const css = { color: this.element.config.iconColor.value };

            if (this.element.config.alignment.value === 'center') {
                css.marginRight = '0';
                css.marginBototm = '5px';
            }

            return css;
        },
    },

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            },
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-usp');
            this.initElementData('moorl-usp');
        },
    },
});
