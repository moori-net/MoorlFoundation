const {Criteria} = Shopware.Data;
const {get, set} = Shopware.Utils.object;

Shopware.Mixin.register('moorl-component-config', {
    data() {
        return {
            pluginName: null,
            demoName: 'standard',

            allowEdit: true,
            allowInlineEdit: true,
            allowCreate: true,
            allowDelete: true,
            showSelection: true,
        };
    },

    methods: {
        initComponentConfig(key) {
            const pluginConfig = MoorlFoundation.ModuleHelper.getByEntity(this.entity);
            this.pluginName = pluginConfig.pluginName ?? null;
            this.demoName = pluginConfig.demoName ?? 'standard';

            const componentConfig = pluginConfig?.componentConfig?.[key];
            if (componentConfig) {
                this.allowEdit = componentConfig.allowEdit ?? true;
                this.allowInlineEdit = componentConfig.allowInlineEdit ?? true;
                this.allowCreate = componentConfig.allowCreate ?? true;
                this.allowDelete = componentConfig.allowDelete ?? true;
                this.showSelection = componentConfig.showSelection ?? true;
            }
        }
    }
});
