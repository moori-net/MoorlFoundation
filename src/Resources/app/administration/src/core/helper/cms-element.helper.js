const {Criteria} = Shopware.Data;
const {merge, cloneDeep} = Shopware.Utils.object;

import ctaBanner from './cms-element/cta-banner';

const defaultConfigs = {
    'cta-banner': ctaBanner
};

export default class CmsElementHelper {
    static enrichCmsDefaultConfig(defaultConfig) {
        for (const [property, field] of Object.entries(defaultConfig)) {
            field.source = field.source ?? 'static';
            field.flags = field.flags ?? {};

            if (field.entity?.name) {
                field.type = 'association';

                if (field.value === undefined) {
                    if (field.entity.name.slice(-1) === "s") {
                        field.value = [];
                    } else {
                        field.value = null;
                    }
                }

                if (Array.isArray([field.value])) {
                    field.relation = 'one_to_many';
                } else {
                    field.relation = 'many_to_one';
                }

                if (!Array.isArray(field.entity.associations)) {
                    field.entity.associations = [];
                    switch (field.entity.name) {
                        case 'product':
                            field.entity.associations.push('cover.media');
                            break;
                        case 'category':
                            field.entity.associations.push('media');
                            break;
                        default:
                    }
                }

                const criteria = new Criteria();

                for (const association of field.entity.associations) {
                    criteria.addAssociation(association);
                }

                field.entity.criteria = criteria;
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
        }
    }

    static getCmsElementConfig({icon, plugin, name, parent, defaultConfig = {}}) {
        if (defaultConfigs[parent] !== undefined) {
            merge(defaultConfig, defaultConfigs[parent]);
        }

        CmsElementHelper.enrichCmsDefaultConfig(defaultConfig);

        const abstractComponent = `moorl-abstract-cms-${parent}`;

        return {
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

    static getPlaceholderMedia() {
        const imagePool = [
            'preview_glasses_large',
            'preview_mountain_large',
            'preview_camera_large',
            'preview_plant_large',
        ];

        const index = Math.floor(Math.random()*imagePool.length);

        return {
            url: `administration/administration/static/img/cms/${imagePool[index]}.jpg`,
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
