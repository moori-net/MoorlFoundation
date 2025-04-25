const CustomFieldDataProviderService = Shopware.Service('customFieldDataProviderService');
const CmsPageTypeService = Shopware.Service('cmsPageTypeService');
const SearchTypeService = Shopware.Service('searchTypeService');

export default class ModuleHelper {
    static registerModule({
                              icon,
                              name,
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
                              color
                          }) {
        if (name) {
            listPath = listPath ?? name.replace(/-/g, '.') + '.list';
            const privilege = `${entity}:read`;
            let parentPath;
            let navItem;

            if (color === undefined) {
                const defaultColors = {
                    'sw-catalogue': '#57d9a3',
                    'sw-content': '#ff3d58',
                    'sw-customer': '#F88962',
                    'sw-marketing': '#ffd700',
                    'sw-orders': '#a092f0',
                }

                color = defaultColors[navigationParent] ?? '#000000';
            }

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

        setTimeout(() => {
            MoorlFoundation.AsyncModuleHelper.registerModule({
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
                navigationParent,
                color,
                listPath
            });
        }, 100);
    }
}
