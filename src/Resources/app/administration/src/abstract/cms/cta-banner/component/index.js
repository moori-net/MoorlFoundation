import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-abstract-cms-cta-banner', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],
    inject: ['repositoryFactory'],

    data() {
        return {
            isLoading: true,
            cache: {}
        };
    },

    computed: {
        elementType() {
            return this.element.type;
        },

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
                entity: this.currentEntity.name,
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

        name() {
            return this.baseData.name
        },

        bannerTitle() {
            return `<${this.titleTag}>${this.name}</${this.titleTag}>`;
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
        },

        repository() {
            return this.repositoryFactory.create(this.currentEntity.name);
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.cmsElementMapping = this.cmsElements[this.elementType]?.cmsElementMapping ?? {};

            this.$watch(() => this.getValue(this.currentType), () => {
                this.getItem();
            });

            this.isLoading = false;
        },

        getItem() {
            if (
                !this.currentType ||
                !this.getValue(this.currentType) ||
                !this.currentEntity?.criteria
            ) {
                return;
            }

            this.repository
                .get(this.getValue(this.currentType), Shopware.Context.api, this.currentEntity.criteria)
                .then(item => {
                    this.element.data[this.currentType] = item;
                    this.isLoading = false;
                });
        },

        getValue(key) {
            return this.element.config?.[key]?.value ?? null;
        }
    }
});
