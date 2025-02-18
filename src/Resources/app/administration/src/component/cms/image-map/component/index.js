const { Component, Application, Mixin, Filter, Utils } = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-moorl-image-map', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        captionCss() {
            const css = {color:this.element.config.boxColor.value}

            if(this.element.config.textShadowActive.value){
                css.textShadow = '2px 2px 15px #000'
            }

            return css;
        },

        mediaUrl() {
            const elemData = this.element.data.media;
            const mediaSource = this.element.config.media.source;

            if (mediaSource === 'mapped') {
                const demoMedia = this.getDemoValue(this.element.config.media.value);

                if (demoMedia && demoMedia.url) {
                    return demoMedia.url;
                }

                return this.assetFilter('administration/static/img/cms/preview_mountain_large.jpg');
            }

            if (elemData && elemData.id) {
                return this.element.data.media.url;
            }

            if (elemData && elemData.url) {
                return this.assetFilter(elemData.url);
            }

            return this.assetFilter('administration/static/img/cms/preview_mountain_large.jpg');
        },

        assetFilter() {
            return Filter.getByName('asset');
        }
    },

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            }
        },

        mediaConfigValue(value) {
            const mediaId = Utils.get(this.element, 'data.media.id');
            const isSourceStatic = Utils.get(this.element, 'config.media.source') === 'static';

            if (isSourceStatic && mediaId && value !== mediaId) {
                this.element.config.media.value = mediaId;
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-image-map');
            this.initElementData('moorl-image-map');
        }
    }
});
