const {Criteria} = Shopware.Data;

Shopware.Component.override('sw-cms-detail', {
    computed: {
        cmsPageTypeSettings() {
            const pluginConfig = Shopware.Store.get('moorlProxy').getByPageType(this.page.type);

            if (pluginConfig === undefined) {
                return this.$super('cmsPageTypeSettings');
            }

            return {
                entity: pluginConfig.entity,
                mode: 'single'
            };
        },
    },

    methods: {
        onDemoEntityChange(demoEntityId) {
            const demoMappingType = this.cmsPageTypeSettings?.entity;
            const pluginConfig = Shopware.Store.get('moorlProxy').getByEntity(demoMappingType);

            if (pluginConfig === undefined) {
                return this.$super('onDemoEntityChange');
            }

            if (demoEntityId) {
                this.cmsPageState.removeCurrentDemoEntity();
                this.cmsPageState.removeCurrentDemoProducts();
            }

            this.loadProxyDemoEntity(demoEntityId, pluginConfig.entity);
        },

        async loadProxyDemoEntity(demoEntityId, entity) {
            const criteria = new Criteria(1, 1);

            if (demoEntityId) {
                criteria.setIds([demoEntityId]);
            }

            const response = await this.repositoryFactory.create(entity).search(criteria);
            const demoEntity = response[0];

            this.demoEntityId = demoEntity.id;
            this.cmsPageState.setCurrentDemoEntity(demoEntity);
        }
    }
});
