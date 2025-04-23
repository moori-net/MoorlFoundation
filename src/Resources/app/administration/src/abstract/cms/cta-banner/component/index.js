import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-cta-banner', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],
    inject: ['repositoryFactory'],

    data() {
        return {
            isLoading: true
        };
    },

    computed: {
        elementType() {
            return this.element.type;
        },

        titleTag() {
            return this.getValue('titleTag');
        },

        currentType() {
            return this.getValue('elementType');
        },

        name() {
            return MoorlFoundation.CmsElementHelper.getBaseData(this.element, 'title', this.currentType, 'name');
        },

        bannerTitle() {
            return `<${this.titleTag}>${this.name}</${this.titleTag}>`;
        },

        bannerDescription() {
            return MoorlFoundation.CmsElementHelper.getBaseData(this.element, 'quote', this.currentType, 'description');
        },

        bannerMediaUrl() {
            return MoorlFoundation.CmsElementHelper.getBaseData(this.element, 'media', this.currentType, 'media')?.url;
        },

        iconMediaUrl() {
            return MoorlFoundation.CmsElementHelper.getBaseData(this.element, 'iconMedia', this.currentType, 'media')?.url;
        },

        elementCss() {
            return {
                'min-height': this.getValue('height'),
                background: this.getValue('elementBackground'),
            };
        },

        backgroundCss() {
            if (this.getValue('videoActive')) return;

            const displayMode = this.getValue('backgroundDisplayMode');
            return {
                'background-image': `url("${this.bannerMediaUrl}")`,
                'background-attachment': this.getValue('backgroundFixed') ? 'fixed' : 'initial',
                'background-position': `${this.getValue('backgroundVerticalAlign')} ${this.getValue('backgroundHorizontalAlign')}`,
                'background-size': displayMode === 'custom'
                    ? `${this.getValue('backgroundSizeX')} ${this.getValue('backgroundSizeY')}`
                    : displayMode,
            };
        },

        overlayCss() {
            return {
                background: this.getValue('overlayBackground'),
                'align-items': this.getValue('boxVerticalAlign'),
                'justify-content': this.getValue('boxHorizontalAlign'),
                'min-height': this.getValue('height'),
                height: '100%',
            };
        },

        boxCss() {
            return {
                display: this.getValue('iconPosition') === 'top' ? 'block' : 'flex',
                margin: this.getValue('boxMargin'),
                padding: this.getValue('boxPadding'),
                background: this.getValue('boxBackground'),
                color: this.getValue('boxColor'),
                width: this.getValue('boxWidth'),
                height: this.getValue('boxHeight'),
                'text-align': this.getValue('boxTextAlign'),
                'border-radius': this.getValue('boxBorderRadius'),
            };
        },

        iconCss() {
            return {
                'font-size': this.getValue('iconFontSize'),
                'margin-bottom': this.getValue('iconMarginBottom'),
                'margin-right': this.getValue('iconMarginRight'),
            };
        },

        boxClass() {
            return this.getValue('boxMaxWidth') ? null : ['reset'];
        }
    },

    watch: {
        cmsPageState: {
            deep: true,
            handler() {
                this.$forceUpdate();
            }
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.cmsElementMapping = this.cmsElements[this.elementType]?.cmsElementMapping ?? {};

            this.isLoading = false;
        },

        getValue(key) {
            return this.element.config?.[key]?.value ?? null;
        }
    }
});
