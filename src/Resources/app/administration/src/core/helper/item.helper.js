import mapping from '../config/form-builder/mapping.js';

const {merge, cloneDeep} = Shopware.Utils.object;

export default class ItemHelper {
    constructor({componentName, entity}) {
        this.componentName = componentName;
        this.entity = entity;
        this.associations = [];
        this.labelProperty = 'name';
        this._init();
    }

    getAssociations() {
        return this.associations;
    }

    getLabelProperty() {
        return this.labelProperty;
    }

    getAiContext() {
        const fields = Shopware.EntityDefinition.get(this.entity).properties;
        const supportedTypes = [
            'string',
            'text',
            'html',
            'int',
            'float',
            'bool',
            'date',
            'datetime',
            'time',
        ];
        const contextFields = {};

        for (const [property, field] of Object.entries(fields)) {
            if (
                !supportedTypes.includes(field.type)
                || ['id', 'versionId', 'createdAt', 'updatedAt', 'translations', 'customFields'].includes(property)
                || field.flags?.computed
                || field.flags?.write_protected
            ) {
                continue;
            }

            contextFields[property] = {
                type: field.type,
                required: field.flags?.required ?? false,
            };
        }

        return {
            entity: this.entity,
            labelProperty: this.labelProperty,
            fields: contextFields,
        };
    }

    getAiPromptSuggestions() {
        const contextFields = this.getAiContext().fields;
        const entityMapping = MoorlFoundation.ModuleHelper.getEntityMapping(this.entity) ?? {};
        const effectiveMapping = merge(cloneDeep(mapping), cloneDeep(entityMapping));

        for (const [field, config] of Object.entries(entityMapping)) {
            if (
                !config
                || typeof config !== 'object'
                || !Object.prototype.hasOwnProperty.call(config, 'aiPromptSuggestions')
            ) {
                continue;
            }

            effectiveMapping[field].aiPromptSuggestions = cloneDeep(config.aiPromptSuggestions);
        }

        return Object.entries(effectiveMapping).flatMap(([field, config]) => {
            if (
                !contextFields[field]
                || config?.hidden === true
                || !Array.isArray(config?.aiPromptSuggestions)
            ) {
                return [];
            }

            return config.aiPromptSuggestions.flatMap((suggestion) => {
                const snippetKey = typeof suggestion === 'string'
                    ? suggestion
                    : suggestion?.snippetKey;

                if (typeof snippetKey !== 'string' || snippetKey.trim() === '') {
                    return [];
                }

                const buttonSnippetKey = typeof suggestion?.buttonSnippetKey === 'string'
                    && suggestion.buttonSnippetKey.trim() !== ''
                    ? suggestion.buttonSnippetKey.trim()
                    : undefined;

                return [{
                    field,
                    snippetKey: snippetKey.trim(),
                    ...(buttonSnippetKey ? {buttonSnippetKey} : {}),
                }];
            });
        });
    }

    getAiImageUrls(item) {
        const fields = Shopware.EntityDefinition.get(this.entity).properties;
        const imageUrls = [];

        for (const [property, field] of Object.entries(fields)) {
            if (field.type !== 'association' || field.entity !== 'media') {
                continue;
            }

            const url = item[property]?.url;

            if (typeof url === 'string' && url.trim() !== '') {
                imageUrls.push(url);
            }
        }

        return [...new Set(imageUrls)];
    }

    hasSeoUrls() {
        return this.associations.indexOf("seoUrls") !== -1;
    }

    _init() {
        const pluginConfig = MoorlFoundation.ModuleHelper.getByEntity(this.entity);

        this.labelProperty = pluginConfig.labelProperty ?? 'name';

        const fields = Shopware.EntityDefinition.get(this.entity).properties;

        for (const [property, field] of Object.entries(fields)) {
            if (field.type === 'association' && field.relation === 'many_to_one' && field.entity === 'media') {
                this.associations.push(property);
                continue;
            }

            if (field.type === 'association' && field.relation !== 'many_to_one') {
                if (field.entity === 'product') {
                    this.associations.push(`${property}.options.group`);
                    this.associations.push(`${property}.cover`);
                } else {
                    this.associations.push(property);
                }
            }
        }
    }
}
