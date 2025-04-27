Shopware.Mixin.register('moorl-abstract-cms-element', {
    mixins: [Shopware.Mixin.getByName('cms-element')],
    inject: ['repositoryFactory'],

    data() {
        return {
            isLoading: true,
            cmsElementMapping: null,
            cmsElementEntity: null,
            cache: {}
        };
    },

    computed: {
        elementType() {
            return this.element.type;
        },
    },

    methods: {
        initBase() {
            const config = MoorlFoundation.CmsElementHelper.getConfig(this.elementType);

            this.cmsElementMapping = config.cmsElementMapping ?? {};
            this.cmsElementEntity = config.cmsElementEntity ?? {};
        },

        initCmsComponent() {
            this.initBase();

            for (const [property, field] of Object.entries(this.element.config)) {
                if (field.entity) {
                    this.$watch(() => this.getValue(property), () => {
                        this.fetchEntityData(property);
                    });
                }

                if (this.element.data[property] === undefined) {
                    this.fetchEntityData(property);
                }
            }

            this.isLoading = false;
        },

        initCmsConfig() {
            this.initBase();

            // Mandatory for entity CMS page config
            this.initElementConfig(this.elementType);

            this.isLoading = false;
        },

        async fetchEntityData(property) {
            const field = this.element.config[property];
            if (!field || !field.entity || !field.value) {
                this.element.data[property] = undefined;
                return;
            }

            const {name: entityName, criteria} = field.entity;
            const repository = this.repositoryFactory.create(entityName);

            try {
                let result;

                if (Array.isArray(field.value)) {
                    criteria.setIds(field.value);
                    result = await repository.search(criteria, Shopware.Context.api);
                } else {
                    result = await repository.get(field.value, Shopware.Context.api, criteria);
                }

                this.element.data[property] = result;
            } catch (e) {
                console.error(`[fetchEntityData] Failed for "${property}"`, e);
            }
        },

        getValue(key) {
            return this.element.config?.[key]?.value ?? null;
        },

        getData(item) {
            if (this.cache[item.id] !== undefined) {
                return this.cache[item.id];
            }

            this.cache[item.id] = MoorlFoundation.CmsElementHelper.getItemData({
                item,
                entity: this.cmsElementEntity.entity
            });

            return this.cache[item.id];
        },
    }
});
