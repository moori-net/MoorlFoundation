Shopware.State.registerModule('moorlFoundationState', {
    namespaced: true,

    state: {
        plugins: []
    },

    mutations: {
        setPlugins(state, plugins) {
            state.plugins = plugins;
        }
    },

    actions: {},
});
