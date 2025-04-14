Shopware.Store.register({
    id: 'moorlFoundationState',

    state: () => ({
        unlocked: false,
        unlockInfoSeen: false,
        plugins: [],
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
        reset() {}
    },
});
