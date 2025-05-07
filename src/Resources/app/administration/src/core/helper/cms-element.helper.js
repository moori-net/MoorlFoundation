import MappingHelper from "./mapping.helper";

const {Criteria} = Shopware.Data;
const {mergeWith} = Shopware.Utils.object;
const {get} = Shopware.Utils;

import ctaBanner from './cms-element/cta-banner';
import listing from './cms-element/listing';

const defaultCmsElementMappings = {
    listing,
    'cta-banner': ctaBanner
};

export default class CmsElementHelper {
    static propertyMapping = {
        category: {
            name: ['translated.name', 'name'],
            description: ['translated.description', 'description'],
            media: 'media',
        },
        product: {
            name: ['translated.name', 'name'],
            description: ['translated.description', 'description'],
            media: 'cover.media',
        },
        default: {
            name: ['translated.name', 'name'],
            description: ['translated.description', 'description'],
            media: 'media',
        },
    };

    static cmsElementConfigCache = {};

    static fetchCmsElement(parent, cmsElementMapping) {
        if (defaultCmsElementMappings[parent] !== undefined) {
            const customMerge = (objValue, srcValue) => {
                if (Array.isArray(objValue)) {return srcValue;}
            };

            cmsElementMapping = mergeWith({}, defaultCmsElementMappings[parent], cmsElementMapping, customMerge);
        }

        MappingHelper.enrichMapping(cmsElementMapping);

        const defaultConfig = MappingHelper.getCmsDefaultConfig(cmsElementMapping);
        const component = `moorl-abstract-cms-${parent}`;

        return {cmsElementMapping, defaultConfig, component};
    }

    static getConfig(name, key) {
        const config = this.cmsElementConfigCache[name] ?? {};
        if (key !== undefined) {
            return config[key] ?? null;
        }
        return config;
    }

    static registerCmsElement({icon, plugin, name, label, parent, cmsElementEntity, cmsElementMapping = {}}) {
        if (cmsElementEntity !== undefined) {
            cmsElementEntity.criteria = MappingHelper.getEntityCriteria(cmsElementEntity);

            if (cmsElementEntity.propertyMapping !== undefined) {
                this.propertyMapping[cmsElementEntity.entity] = cmsElementEntity.propertyMapping;
            }
        }

        const fetched = CmsElementHelper.fetchCmsElement(parent, cmsElementMapping);

        plugin = plugin ?? 'MoorlFoundation';
        icon = icon ?? 'regular-view-grid';

        this.cmsElementConfigCache[name] = {
            cmsElementEntity,
            plugin,
            icon,
            ...fetched
        };

        const cmsElementConfig = {
            cmsElementEntity,
            defaultData: CmsElementHelper.getDefaultData(),
            plugin,
            icon,
            name,
            label: label ?? `${fetched.component}.name`,
            configComponent: `${fetched.component}-config`,
            previewComponent: true, // TODO: Remove the hack from core template
            ...fetched
        };

        Shopware.Application.getContainer('service').cmsService.registerCmsElement(cmsElementConfig);
    }

    static getDefaultData() {
        return {
            category: {
                name: 'Demo Category',
                description: CmsElementHelper.getPlaceholderText(),
                media: CmsElementHelper.getPlaceholderMedia(),
            },
            product: {
                name: 'Demo Product',
                description: CmsElementHelper.getPlaceholderText(),
                cover: {
                    media: CmsElementHelper.getPlaceholderMedia(),
                },
            },
            default: {
                name: 'Default Item',
                description: CmsElementHelper.getPlaceholderText(),
                media: CmsElementHelper.getPlaceholderMedia(),
            }
        }
    }

    static getItemData({item, entity}) {
        const data = CmsElementHelper.getDefaultData()['default'];

        if (item === undefined || entity === undefined) {
            return data;
        }

        let propertyMapping = this.propertyMapping[entity];
        if (propertyMapping === undefined) {
            propertyMapping = this.propertyMapping['default'];
        }

        const types = ['name', 'description', 'media'];

        for(const type of types) {
            if (propertyMapping[type] === undefined) {
                continue;
            }

            data[type] = CmsElementHelper.getItemPropertyData(item, propertyMapping[type], data[type]);
        }

        return data;
    }

    static getItemPropertyData(item, paths, fallback = null) {
        if (!Array.isArray(paths)) {paths = [paths];}

        for (const path of paths) {
            const value = get(item, path);
            if (value) {
                return value;
            }
        }

        return fallback;
    }

    static getBaseData({element, entity, properties, dataSource}) {
        const baseData = CmsElementHelper.getItemData({
            item: element.data[dataSource],
            entity
        });

        if (properties === undefined) {
            return baseData;
        }

        for (const property of properties) {
            if (property.config === undefined) {
                continue;
            }

            const id = property.data ?? property.config;

            if (
                typeof element.data[property.config] === 'object' &&
                element.data[property.config] !== null
            ) {
                baseData[id] = element.data[property.config];
                continue;
            }

            if (element.config[property.config].value) {
                if (!element.config[property.config].entity) {
                    baseData[id] = element.config[property.config].value;
                    continue;
                }
            }

            if (baseData[id] === undefined) {
                baseData[id] = null;
            }
        }

        return baseData;
    }

    static getPlaceholderMedia() {
        const assetFilter = Shopware.Filter.getByName('asset');

        const imagePool = [
            'preview_glasses_large',
            'preview_mountain_large',
            'preview_camera_large',
            'preview_plant_large',
        ];

        const index = Math.floor(Math.random()*imagePool.length);

        return {
            url: assetFilter(`administration/administration/static/img/cms/${imagePool[index]}.jpg`),
            alt: 'Lorem Ipsum dolor'
        }
    }

    static getPlaceholderText(length = 30) {
        const lorem = `Lorem ipsum dolor sit amet, consectetur adipiscing elit.
        Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
        Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi 
        ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit 
        in voluptate velit esse cillum dolore eu fugiat nulla pariatur. 
        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia 
        deserunt mollit anim id est laborum.`;

        const words = lorem.split(/\s+/);
        const result = [];

        while (result.length < length) {
            result.push(...words);
        }

        return result.slice(0, length).join(' ').replace(/\s+/g, ' ').trim() + '.';
    }
}
