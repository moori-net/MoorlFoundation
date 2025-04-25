export default class AsyncModuleHelper {
    static pluginConfigCache = [];
    static entityMappingCache = {};

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
            if (!this.entityMappingCache[entity]) {
                this.entityMappingCache[entity] = {};
            }

            Object.entries(entityMapping).forEach(([fieldKey, fieldConfig]) => {
                if (!this.entityMappingCache[entity][fieldKey]) {
                    this.entityMappingCache[entity][fieldKey] = fieldConfig;
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

    static getEntityMapping(entity) {
        return this.entityMappingCache[entity];
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
