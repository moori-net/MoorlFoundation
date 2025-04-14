Shopware.Store.register({
    id: 'moorlProxy',

    state: () => ({
        pluginConfigs: []
    }),

    actions: {
        addPluginConfig(pluginConfig) {
            this.pluginConfigs.push(pluginConfig);
        },
        getByPageType(pageType) {
            return this.pluginConfigs.find(
                (pluginConfig) => pluginConfig.name === pageType
            );
        },
        getByEntity(entity) {
            return this.pluginConfigs.find(
                (pluginConfig) => pluginConfig.entity === entity
            );
        }
    },
});
