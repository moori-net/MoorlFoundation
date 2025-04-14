const CustomFieldDataProviderService = Shopware.Service('customFieldDataProviderService');
const CmsPageTypeService = Shopware.Service('cmsPageTypeService');
const SearchTypeService = Shopware.Service('searchTypeService');

class MoorlProxyService {
    registerPlugin(pluginConfig) {
        if (pluginConfig.entity === undefined) {
            console.log("You need at least an entity name");
        }

        CustomFieldDataProviderService.addEntityName(pluginConfig.entity);

        if (
            pluginConfig.name !== undefined &&
            pluginConfig.icon !== undefined &&
            pluginConfig.title !== undefined
        ) {
            CmsPageTypeService.register({
                name: pluginConfig.name,
                icon: pluginConfig.icon,
                title: pluginConfig.title
            });
        }

        if (
            pluginConfig.placeholderSnippet !== undefined &&
            pluginConfig.listingRoute !== undefined
        ) {
            SearchTypeService.upsertType(pluginConfig.entity, {
                entityName: pluginConfig.entity,
                placeholderSnippet: pluginConfig.placeholderSnippet,
                listingRoute: pluginConfig.listingRoute
            });
        }

        Shopware.Store.get('moorlProxy').addPluginConfig(pluginConfig);
    }
}

Shopware.Application.addServiceProvider('moorlProxyService', () => new MoorlProxyService());

export {MoorlProxyService};
