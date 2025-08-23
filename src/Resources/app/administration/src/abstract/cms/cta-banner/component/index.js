import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-cta-banner', {
    template,

    mixins: [Shopware.Mixin.getByName('moorl-abstract-cms-element')],

    computed: {
        currentType() {
            return this.getValue('elementType');
        },

        currentEntity() {
            return this.element.config[this.currentType]?.entity;
        },

        titleTag() {
            return this.getValue('titleTag');
        },

        baseData() {
            return MoorlFoundation.CmsElementHelper.getBaseData({
                entity: this.currentEntity?.name,
                element: this.element,
                dataSource: this.currentType,
                properties: [
                    {config: 'title', data: 'name'},
                    {config: 'quote', data: 'description'},
                    {config: 'media', data: 'media'},
                    {config: 'iconMedia', data: undefined},
                ]
            });
        },

        bannerTitle() {
            return `<${this.titleTag}>${this.baseData.name}</${this.titleTag}>`;
        },

        bannerDescription() {
            return this.baseData.description
        },

        bannerMediaUrl() {
            return this.baseData.media?.url;
        },

        iconMediaUrl() {
            return this.baseData.iconMedia?.url;
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

    created() {
        this.initCmsComponent();
    }
});
