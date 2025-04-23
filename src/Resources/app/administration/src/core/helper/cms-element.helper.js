const {Criteria} = Shopware.Data;
const {merge, cloneDeep} = Shopware.Utils.object;

import ctaBanner from './cms-element/cta-banner';

const defaultCmsElementMappings = {
    'cta-banner': ctaBanner
};

export default class CmsElementHelper {
    static enrichCmsElementMapping(cmsElementMapping) {
        const defaultConfig = {};

        for (const [property, field] of Object.entries(cmsElementMapping)) {
            field.source = field.source ?? 'static';
            field.flags = field.flags ?? {};

            defaultConfig[property] = {
                source: field.source,
            };

            if (field.entity) {
                field.type = 'association';

                if (field.value === undefined) {
                    if (field.entity.slice(-1) === "s") {
                        field.value = [];
                    } else {
                        field.value = null;
                    }
                }

                if (Array.isArray([field.value])) {
                    field.relation = 'many_to_one';
                    field.localField = property;
                } else {
                    field.relation = 'one_to_many';
                }

                if (!Array.isArray(field.associations)) {
                    field.associations = [];
                    switch (field.entity) {
                        case 'product':
                            field.associations.push('cover.media');
                            break;
                        case 'category':
                            field.associations.push('media');
                            break;
                        default:
                    }
                }

                const criteria = new Criteria();

                for (const association of field.associations) {
                    criteria.addAssociation(association);
                }

                defaultConfig[property].entity = {
                    name: field.entity,
                    criteria: criteria
                };
            } else if (field.value === undefined) {
                field.type = 'string';
                field.value = null;
            }

            if (!field.type) {
                field.type = typeof field.value;
            }

            if (field.type === 'object') {
                if (Array.isArray(field.value)) {
                    field.type = 'list';
                } else if (field.value === null) {
                    field.type = 'string';
                }
            }

            defaultConfig[property].value = field.value;
        }

        return defaultConfig;
    }

    static getCmsElementConfig({icon, plugin, name, parent, cmsElementMapping = {}}) {
        if (defaultCmsElementMappings[parent] !== undefined) {
            cmsElementMapping = merge(
                {},
                defaultCmsElementMappings[parent],
                cmsElementMapping,
            );
        }

        const defaultConfig = CmsElementHelper.enrichCmsElementMapping(cmsElementMapping);
        const abstractComponent = `moorl-abstract-cms-${parent}`;

        return {
            cmsElementMapping,
            defaultConfig,
            defaultData: CmsElementHelper.getDefaultData(),
            plugin: plugin ?? 'MoorlFoundation',
            icon: icon ?? 'regular-marketing',
            name: name,
            label: `${abstractComponent}.name`,
            component: abstractComponent,
            configComponent: `${abstractComponent}-config`,
            previewComponent: true
        }
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
        }
    }

    static getBaseData(element, configProperty, dataProperty, type) {
        const config = element.config;
        let data = element.data;

        if (typeof data[configProperty] === 'object') {
            return data[configProperty];
        }

        if (config[configProperty].value) {
            if (!config[configProperty].entity) {
                return config[configProperty].value;
            }
        }


        if (data[dataProperty] === undefined) {
            data = CmsElementHelper.getDefaultData();
        }

        let item = data[dataProperty];

        if (item === undefined) {
            item = data['product'];
        }

        if (item.translated && item.translated[type]) {
            return item.translated[type];
        }

        if (item.cover && item.cover[type]) {
            return item.cover[type];
        }

        if (item[type]) {
            return item[type];
        }

        for (const [key, prop] of Object.entries(item)) {
            if (!prop) {
                continue;
            }

            if (typeof prop[type] === 'object') {
                return prop[type];
            }
        }

        return null;
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
