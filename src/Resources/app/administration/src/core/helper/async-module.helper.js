export default class AsyncModuleHelper {
    static pluginConfigCache = [];
    static entityMappingsCache = {};

    static registerModule({
                              icon,
                              name,
                              entity,
                              title,
                              position,
                              defaultSearchConfiguration,
                              placeholderSnippet,
                              demoName,
                              pageType,
                              pluginName,
                              entityMapping,
                              properties,
                              cmsElements,
                              listPath,
                              navigationParent,
                              color
                          }) {
        if (entityMapping) {
            if (!this.entityMappingsCache[entity]) {
                this.entityMappingsCache[entity] = {};
            }

            Object.entries(entityMapping).forEach(([fieldKey, fieldConfig]) => {
                if (!this.entityMappingsCache[entity][fieldKey]) {
                    this.entityMappingsCache[entity][fieldKey] = fieldConfig;
                } else {
                    console.warn(`[CustomEntityMapping] Feld "${fieldKey}" für Entity "${entity}" wurde bereits registriert und wird ignoriert.`);
                }
            });
        }

        listPath = listPath ?? (name ? name.replace(/-/g, '.') + '.list' : undefined);

        this.pluginConfigCache.push({
            pageType,
            entity,
            properties,
            pluginName,
            demoName,
            listPath
        });

        for (const cmsElement of cmsElements) {
            cmsElement.plugin = pluginName;
            if (cmsElement.cmsElementEntity) {
                cmsElement.cmsElementEntity.entity = entity;
            }
            MoorlFoundation.CmsElementHelper.registerCmsElement(cmsElement);
        }
    }

    static addPluginConfig(pluginConfig) {
        this.pluginConfigCache.push(pluginConfig);
    }

    static getByPageType(pageType) {
        return this.pluginConfigCache.find(
            (pluginConfig) => pluginConfig.pageType === pageType
        );
    }

    static getByEntity(entity) {
        return this.pluginConfigCache.find(
            (pluginConfig) => pluginConfig.entity === entity
        );
    }
}
