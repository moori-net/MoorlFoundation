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
        }
    },

    methods: {
        onLoadCmsElementConfig() {
            this.cmsElementConfigRepository
                .get(this.cmsElementConfigId)
                .then((entity) => {
                    this.element.config = entity.config;
                    this.element.data = entity.data;
                    this.element.type = entity.type;
                    this.$emit('element-update', this.element);
                });
        },
    },
});
