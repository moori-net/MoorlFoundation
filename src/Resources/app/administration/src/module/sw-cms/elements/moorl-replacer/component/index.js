const { Criteria } = Shopware.Data;

import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('sw-cms-el-moorl-replacer', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    inject: ['repositoryFactory'],

    data() {
        return {
            cmsElementConfigId: null,
        };
    },

    computed: {
        cmsElementConfigRepository() {
            return this.repositoryFactory.create('moorl_cms_element_config');
        },
        cmsElementConfigCriteria() {
            return new Criteria();
        },
        mediaUrl() {
            const context = Shopware.Context.api;
            return `${context.assetsPath}administration/administration/static/img/cms/preview_mountain_large.jpg`;
        },
        elementCss() {
            return {
                'background-image': 'url("' + this.mediaUrl + '")',
            };
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
            this.initElementConfig('moorl-replacer');
        },

        onLoadCmsElementConfig() {
            this.cmsElementConfigRepository
                .get(
                    this.cmsElementConfigId,
                    Shopware.Context.api,
                    this.cmsElementConfigCriteria
                )
                .then((entity) => {
                    this.element.config = entity.config;
                    this.element.data = entity.data;
                    this.element.type = entity.type;
                    this.$emit('element-update', this.element);
                });
        },
    },
});
