const CustomFieldDataProviderService = Shopware.Service('customFieldDataProviderService');
const CmsPageTypeService = Shopware.Service('cmsPageTypeService');
const SearchTypeService = Shopware.Service('searchTypeService');

export default class ModuleHelper {
    static pluginConfigCache = [];
    static entityMappingCache = {};
    static defaultColors = {
        'sw-catalogue': '#57d9a3',
        'sw-content': '#ff3d58',
        'sw-customer': '#F88962',
        'sw-marketing': '#ffd700',
        'sw-orders': '#a092f0',
    };
    static defaultIcons = {
        'sw-catalogue': 'regular-products',
        'sw-content': 'regular-content',
        'sw-customer': 'regular-users',
        'sw-marketing': 'regular-megaphone',
        'sw-orders': 'shopping-bag',
    };

    static registerModule({
                              icon,
                              name,
                              description = `${name}.description`,
                              entity,
                              title = `global.entities.${entity}`,
                              position,
                              defaultSearchConfiguration,
                              placeholderSnippet = 'global.sw-search-bar.placeholderSearchField',
                              demoName,
                              pageType,
                              pluginName,
                              entityMapping,
                              properties,
                              listPath,
                              cmsElements = [],
                              navigationParent = 'sw-settings',
                              color,
                              moduleConfig = null,
                              entityOverride = null,
    }) {
        color = color ?? this.defaultColors[navigationParent] ?? '#9aa8b5';
        icon = icon ?? this.defaultIcons[navigationParent] ?? 'regular-cog';

        if (name) {
            listPath = listPath ?? name.replace(/-/g, '.') + '.list';
            const privilege = `${entity}:read`;
            let parentPath;
            let navItem;

            if (navigationParent === 'sw-settings') {
                parentPath = 'sw.settings.index';
                navItem = {
                    settingsItem: {
                        privilege,
                        to: listPath,
                        group: 'plugins',
                        icon
                    }
                };
            } else {
                parentPath = listPath;
                navItem = {
                    navigation: [{
                        label: title,
                        icon,
                        path: listPath,
                        position,
                        parent: navigationParent
                    }]
                };
            }

            const moduleConfig = {
                type: 'plugin',
                name,
                description,
                entity,
                title,
                icon,
                color,
                routes: {
                    list: {
                        component: `${name}-list`,
                        path: 'list',
                        meta: { privilege, parentPath }
                    },
                    detail: {
                        component: `${name}-detail`,
                        path: 'detail/:id',
                        meta: { privilege, parentPath: listPath }
                    },
                    create: {
                        component: `${name}-detail`,
                        path: 'create',
                        meta: { privilege, parentPath: listPath }
                    }
                },
                defaultSearchConfiguration,
                ...navItem
            };

            for (const suffix of ['list', 'detail']) {
                Shopware.Component.extend(`${name}-${suffix}`, `moorl-abstract-page-${suffix}`, {
                    data: () => ({ entity })
                });
            }

            Shopware.Module.register(name, moduleConfig);

            CustomFieldDataProviderService.addEntityName(entity);

            if (defaultSearchConfiguration) {
                SearchTypeService.upsertType(entity, { entityName: entity, placeholderSnippet, listPath });
            }
        }

        if (pageType) {
            CmsPageTypeService.register({ name: pageType, icon, title });
        }

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
            listPath,
            moduleConfig,
            entityOverride
        });

        for (const cmsElement of cmsElements) {
            cmsElement.plugin = cmsElement.plugin ?? pluginName;
            if (cmsElement.cmsElementEntity) {
                cmsElement.cmsElementEntity.entity = cmsElement.cmsElementEntity.entity ?? entity;
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
