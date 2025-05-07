const {Criteria} = Shopware.Data;

export default class MappingHelper {
    static enrichMapping(mapping) {
        for (const [property, field] of Object.entries(mapping)) {
            if (field.hidden) {
                delete mapping[property];
                continue;
            }

            if (field.entity) {
                field.type = 'association';

                if (field.value === undefined) {
                    if (property.slice(-1) === "s") {
                        field.value = [];
                    } else {
                        field.value = null;
                    }
                }

                if (Array.isArray(field.value)) {
                    field.relation = 'one_to_many';
                } else {
                    field.relation = 'many_to_one';
                    field.localField = property;
                }
            } else if (field.value === undefined) {
                field.type = 'string';
                field.value = null;
            }

            if (!field.type) {
                field.type = typeof field.value;
            }

            if (field.type === 'object') {
                if (Array.isArray(field.value)) {
                    field.type = 'json_list';
                } else if (field.value === null) {
                    field.type = 'string';
                } else {
                    field.type = 'json_object';
                }
            }
        }

        return mapping;
    }

    static getCmsDefaultConfig(mapping) {
        const defaultConfig = {};

        for (const [property, field] of Object.entries(mapping)) {
            defaultConfig[property] = {
                source: field.source ?? 'static'
            };

            if (field.entity) {
                defaultConfig[property].entity = {
                    name: field.entity,
                    criteria: MappingHelper.getEntityCriteria(field)
                };
            }

            defaultConfig[property].value = field.value;
        }

        return defaultConfig;
    }

    static getEntityCriteria(field) {
        if (!Array.isArray(field.associations)) {
            field.associations = [];
            switch (field.entity) {
                case 'product':
                    field.associations.push('cover.media');
                    break;
                case 'category':
                case 'product_manufacturer':
                    field.associations.push('media');
                    break;
                default:
            }
        }

        const criteria = new Criteria();

        for (const association of field.associations) {
            criteria.addAssociation(association);
        }

        return criteria;
    }
}
