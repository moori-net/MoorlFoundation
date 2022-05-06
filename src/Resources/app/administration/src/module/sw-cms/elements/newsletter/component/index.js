const { Component, Application, Mixin } = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-appflix-newsletter', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {

    },

    computed: {
        newsletterButtonCss(){
            return{
                'color': this.element.config.buttonTextColor.value,
                'background-color': this.element.config.buttonBackground.value
            }
        }
    },

    watch: {

        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            }
        },
    },

    created() {
        this.createdComponent();
    },

    mounted() {
        this.mountedComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('appflix-newsletter');
            this.initElementData('appflix-newsletter');
        }
    }
});
