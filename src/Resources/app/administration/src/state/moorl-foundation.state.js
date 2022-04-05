Shopware.State.registerModule('moorlFoundationState', {
    namespaced: true,

    state: {
        unlocked: false,
        unlockInfoSeen: false,
        plugins: [],
    },

    mutations: {
        setPlugins(state, plugins) {
            state.plugins = plugins;
        },
        setUnlocked(state, value) {
            state.unlocked = value;
        },
        toggleUnlocked(state) {
            state.unlocked = !state.unlocked;
        },
        setUnlockModalSeen(state) {
            state.unlockInfoSeen = true;
        }
    },

    actions: {},
});
