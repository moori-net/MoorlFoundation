const { Criteria } = Shopware.Data;

import template from './sw-cms-slot.html.twig';

Shopware.Component.override('sw-cms-slot', {
    template,

    inject: ['repositoryFactory'],

    data() {
        return {
            cmsElementConfig: null,
            cmsElementConfigId: null,
            showCmsElementConfigSaver: false,
            showCmsElementAnimationModal: false,
        };
    },

    computed: {
        cmsElementConfigRepository() {
            return this.repositoryFactory.create('moorl_cms_element_config');
        },

        cmsElementConfigCriteria() {
            return new Criteria();
        },

        pluginRepository() {
            return this.repositoryFactory.create('plugin');
        },

        pluginCriteria() {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('active', 1));
            return criteria;
        },

        plugins() {
            return Shopware.Store.get('moorlFoundationState').plugins;
        },

        moorlIsUnlocked() {
            return Shopware.Store.get('moorlFoundationState').unlocked;
        },
    },

    methods: {
        getPlugin(element) {
            if (!element.plugin || this.plugins.length === 0) {
                return null;
            }

            return this.plugins.find((item) => item.name === element.plugin);
        },

        async onElementButtonClick() {
            if (this.plugins.length) {
                return this.$super('onElementButtonClick');
            }

            const plugins = await this.pluginRepository.search(
                this.pluginCriteria
            );

            Shopware.Store.get('moorlFoundationState').setPlugins(plugins);

            this.$super('onElementButtonClick');
        },

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
                .get(
                    this.cmsElementConfigId,
                    Shopware.Context.api,
                    this.cmsElementConfigCriteria
                )
                .then((entity) => {
                    this.element.config = entity.config;
                    this.element.type = entity.type;
                    this.element.data = entity.data;
                    this.$emit('element-update', this.element);
                    this.showCmsElementConfigSaver = false;
                });
        },

        getCmsElementConfig() {
            this.cmsElementConfig = this.cmsElementConfigRepository.create(
                Shopware.Context.api
            );
            this.cmsElementConfig.type = this.element.type;
            this.cmsElementConfig.config = this.element.config;
            this.cmsElementConfig.data = this.element.data;
        },
    },
});
