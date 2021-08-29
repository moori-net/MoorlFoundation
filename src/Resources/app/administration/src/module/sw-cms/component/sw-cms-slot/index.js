const {Component} = Shopware;
const Criteria = Shopware.Data.Criteria;

import template from './sw-cms-slot.html.twig';
import './sw-cms-slot.scss';

Component.override('sw-cms-slot', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            cmsElementConfig: null,
            cmsElementConfigId: null,
            showCmsElementConfigSaver: false,
            snippetPrefix: 'sw-cms.component.sw-cms-slot.',
        };
    },

    computed: {
        cmsElementConfigRepository() {
            return this.repositoryFactory.create('moorl_cms_element_config');
        },

        cmsElementConfigCriteria() {
            const criteria = new Criteria();
            //criteria.addFilter(Criteria.equals('type', this.element.type));
            return criteria;
        },
    },

    methods: {
        onCmsElementConfigSaverButtonClick() {
            if (!this.elementConfig.defaultConfig || this.element.locked) {
                return;
            }
            this.getCmsElementConfig();
            this.showCmsElementConfigSaver = true;
        },

        onCloseCmsElementConfigSaverModal() {
            this.showCmsElementConfigSaver = false;
        },

        onSaveCmsElementConfig() {
            this.cmsElementConfigRepository
                .save(this.cmsElementConfig, Shopware.Context.api)
                .then(() => {
                    this.showCmsElementConfigSaver = false;
                });
        },

        onLoadCmsElementConfig() {
            this.cmsElementConfigRepository
                .get(this.cmsElementConfigId, Shopware.Context.api, this.cmsElementConfigCriteria)
                .then((entity) => {
                    this.element.config = entity.config;
                    this.element.type = entity.type;
                    this.element.data = entity.data;
                    this.$emit('element-update', this.element);
                    this.showCmsElementConfigSaver = false;
                });
        },

        getCmsElementConfig() {
            this.cmsElementConfig = this.cmsElementConfigRepository.create(Shopware.Context.api);
            this.cmsElementConfig.type = this.element.type;
            this.cmsElementConfig.config = this.element.config;
            this.cmsElementConfig.data = this.element.data;
        },
    }
});
