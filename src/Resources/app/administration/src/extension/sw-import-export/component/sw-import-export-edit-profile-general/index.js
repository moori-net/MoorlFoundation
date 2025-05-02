Shopware.Component.override('sw-import-export-edit-profile-general', {
    computed: {
        supportedEntities() {
            const supportedEntities = this.$super('supportedEntities');

            const pluginConfig = MoorlFoundation.ModuleHelper.pluginConfigCache ?? [];

            for (let abc of pluginConfig) {
                if (!abc.pluginName) {
                    continue;
                }

                supportedEntities.push({
                    value: abc.entity,
                    label: this.$tc(`global.entities.${abc.entity}`),
                    type: 'import-export',
                });
            }

            return supportedEntities;
        }
    },
});
