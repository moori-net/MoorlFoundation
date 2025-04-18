Shopware.Store.register({
    id: 'moorlFoundationState',

    state: () => ({
        unlocked: false,
        unlockInfoSeen: false,
        plugins: [],
        customEntityMappings: {}
    }),

    actions: {
        setPlugins(plugins) {
            this.plugins = plugins;
        },
        setUnlocked(value) {
            this.unlocked = value;
        },
        toggleUnlocked() {
            this.unlocked = !state.unlocked;
        },
        setUnlockModalSeen() {
            this.unlockInfoSeen = true;
        },
        addCustomEntityMapping(customEntityMapping) {
            for (const [entity, newFields] of Object.entries(customEntityMapping)) {
                if (!this.customEntityMappings[entity]) {
                    this.customEntityMappings[entity] = {};
                }
                Object.entries(newFields).forEach(([fieldKey, fieldConfig]) => {
                    if (!this.customEntityMappings[entity][fieldKey]) {
                        this.customEntityMappings[entity][fieldKey] = fieldConfig;
                    } else {
                        console.warn(`[CustomEntityMapping] Feld "${fieldKey}" für Entity "${entity}" wurde bereits registriert und wird ignoriert.`);
                    }
                });
            }
        },
        getCustomEntityMapping(entity) {
            return this.customEntityMappings[entity];
        },
        reset() {},
    },
});
