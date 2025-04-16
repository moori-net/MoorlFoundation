import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-html-tag', {
    template,

    mixins: [
        Shopware.Mixin.getByName('cms-element'),
        Shopware.Mixin.getByName('placeholder'),
    ],

    computed: {
        elementHTML() {
            let c = this.element.config.content.value;
            if (this.element.config.content.source === 'mapped') {
                c = this.getDemoValue(c);
            }
            let t = this.element.config.tag.value;
            let css = this.element.config.cssClass.value;
            let style = this.element.config.style.value;

            return `<${t} class="${css}" style="${style}">${c}</${t}>`;
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
            this.initElementConfig('moorl-html-tag');
        },
    },
});
