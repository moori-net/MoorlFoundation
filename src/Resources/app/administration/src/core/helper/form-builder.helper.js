import mapping from './form-builder/mapping.js';
import order from './form-builder/order.json';

const {merge, cloneDeep} = Shopware.Utils.object;

export default class FormBuilderHelper {
    static entityLabelProperty = {
        media: 'fileName',
        product: 'productNumber',
        salutation: 'displayName',
        customer: 'customerNumber',
        moorl_sorting: 'label'
    };

    constructor({
                    entity,
                    item,
                    componentName,
                    tc,
                    snippetSrc = 'moorl-foundation'
    }) {
        this.entity = entity ?? componentName;
        this.item = item;
        this.componentName = componentName;
        this.snippetSrc = snippetSrc;

        this.order = order;
        this.pageStruct = { tabs: [] };
        this.mediaOrder = 4999;

        this.masterMapping = undefined;
        this.currency = null;
        this.tax = null;
        this.customFieldSets = [];

        this.translationHelper = new MoorlFoundation.TranslationHelper({componentName, snippetSrc, tc});

        this.initialized = false;
    }

    buildFormStruct() {
        this._init();

        const fields = this.masterMapping ?? Shopware.EntityDefinition.get(this.entity).properties;

        return this._build(fields);
    }

    _build(fields) {
        if (this.pageStruct.tabs.length > 0) {
            console.warn(`[${this.entity}][${this.componentName}] TODO: prevent calling buildFormStruct() multiple times`);
            return this.pageStruct;
        }

        MoorlFoundation.Logger.log('FormBuilderHelper._build', 'fields', fields);

        this._buildImportExportProfile(this.entity, fields);

        for (const [property, field] of Object.entries(fields)) {
            if (
                field.type === 'uuid' ||
                ['createdAt', 'updatedAt', 'translations'].includes(property) ||
                field.flags.runtime !== undefined ||
                field.flags.computed !== undefined
            ) continue;

            const column = this._buildColumn(field, property, fields);
            if (!column) continue;

            this._addColumnToStruct(column, property);
        }

        this._sortStruct();

        MoorlFoundation.Logger.log('FormBuilderHelper._build', 'pageStruct', this.pageStruct);

        return this.pageStruct;
    }

    _buildImportExportProfile(entity, fields, depth = 0, path = "") {
        const typeOrder = ['uuid' ,'boolean', 'int', 'float', 'string', 'text', 'date', 'association'];
        const blacklist = ['createdAt', 'updatedAt', 'translations', 'salesChannel', 'versionId'];
        const whitelist = ['id', 'name', 'url', 'title', 'alt', 'taxRate'];

        function toSnakeCase(str) {
            return str
                .replace(/([a-z0-9])([A-Z])/g, '$1_$2')
                .replace(/([a-zA-Z])([0-9]+)/g, '$1_$2')
                .replace(/\./g, '_')
                .toLowerCase();
        }

        const array = Object.entries(fields)
            .map(([key, value]) => ({ key, ...value }))
            .filter(entry => typeOrder.includes(entry.type))
            .filter(entry => !blacklist.includes(entry.key))
            .filter(entry => depth === 0 || whitelist.includes(entry.key))
            //.filter(entry => depth === 0 || entry.flags.required)
            .sort((a, b) => {
                return typeOrder.indexOf(a.type) - typeOrder.indexOf(b.type);
            });

        const mapping = [];

        for (const index in array) {
            if (array[index].type === 'uuid' && !array[index].flags.primary_key) {
                continue;
            }
            if (array[index].type === 'association') {
                if (depth === 0 && array[index].relation === 'many_to_one') {
                    const aFields = Shopware.EntityDefinition.get(array[index].entity).properties;

                    mapping.push(...this._buildImportExportProfile(array[index].entity, aFields, depth + 1, `${path}${array[index].key}.`));
                }
                continue;
            }
            if (array[index].flags.translatable) {
                mapping.push({
                    key: `${path}translations.DEFAULT.${array[index].key}`,
                    mappedKey: toSnakeCase(path+array[index].key)
                });
            } else {
                mapping.push({
                    key: `${path}${array[index].key}`,
                    mappedKey: toSnakeCase(path+array[index].key)
                });
            }
        }

        for (const index in mapping) {
            mapping[index].position = parseInt(index);
        }

        if (depth === 0) {
            MoorlFoundation.Logger.log('FormBuilderHelper._buildImportExportProfile', 'mapping', mapping);
        }

        return mapping;
    }

    _init() {
        if (this.initialized) {
            return;
        }

        this.mapping = cloneDeep(this.masterMapping ?? mapping);

        let currentOrder = 0;
        for (const [key, config] of Object.entries(this.mapping)) {
            config.order = currentOrder;
            currentOrder += 10;
        }

        if (!this.entity) {
            return;
        }

        const customMapping = MoorlFoundation.ModuleHelper.getEntityMapping(this.entity) ?? {};

        for (const [key, config] of Object.entries(customMapping)) {
            if (typeof config.order === 'string') {
                const orderStr = config.order;

                if (orderStr === 'first') {
                    config.order = 0;
                } else if (orderStr === 'last') {
                    config.order = 9999;
                } else {
                    const match = orderStr.match(/^(before|after):?(.*)$/);
                    if (match) {
                        const [, position, targetKey] = match;
                        const target = this.mapping[targetKey];

                        const baseOrder = target?.order ?? 9999;
                        config.order = position === 'before' ? baseOrder - 1 : baseOrder + 1;

                        if (target) {
                            config.tab ??= target.tab;
                            config.card ??= target.card;
                        }
                    } else {
                        console.error(`[FormBuilderHelper] Invalid string in order:"${orderStr}". Use "first", "last", "before:targetKey" or "after:targetKey"`);
                    }
                }
            }
        }

        merge(this.mapping, customMapping);

        this.initialized = true;
    }

    _buildColumn(field, property, fields) {
        const column = {
            tab: 'undefined',
            card: 'undefined',
            name: property,
            model: undefined,
            order: this.mapping[property]?.order ?? 9999,
            cols: 12
        };

        const attributes = {};

        if (this.mapping[property]) {
            Object.assign(column, this.mapping[property]);
            if (this.mapping[property].attributes) {
                Object.assign(attributes, this.mapping[property].attributes);
            }
        }

        if (column.hidden) return null;

        column.label = this.translationHelper.getLabel('field', property);

        if (column.componentName && field.type !== 'association') {
            // Meteor components have no model value
            if (!column.componentName.startsWith("mt-")) {
                column.model = 'value';
            }
        } else {
            switch (field.type) {
                case 'string':
                    column.componentName = 'mt-text-field';
                    break;
                case 'text':
                    if (!field.flags.allow_html) {
                        column.componentName = 'mt-textarea';
                    } else {
                        column.componentName = 'mt-text-editor';
                    }
                    break;
                case 'html':
                    column.componentName = 'mt-text-editor';
                    break;
                case 'int':
                case 'float':
                case 'number':
                    column.componentName = 'mt-number-field';
                    attributes.numberType = field.type;
                    if (field.type === 'float') {
                        attributes.digits = 8;
                    }
                    break;
                case 'boolean':
                case 'bool':
                    column.componentName = 'mt-switch';
                    attributes.bordered = true;
                    break;
                case 'date':
                    column.componentName = 'mt-datepicker';
                    attributes.dateType = 'date';
                    attributes.size = 'default';
                    break;
                case 'color':
                    column.componentName = 'mt-colorpicker';
                    break;
                case 'code':
                    column.model = 'value';
                    column.componentName = 'sw-code-editor';
                    break;
                case 'object':
                case 'json_object':
                case 'list':
                    if (property.toLowerCase().includes("price")) {
                        column.tab = 'price';
                        column.card = 'price';
                        column.componentName = 'moorl-price-field';
                        attributes.tax = ({tax}) => tax;
                        attributes.currency = ({currency}) => currency;
                    }

                    if (!column.componentName) return null;
                    column.type = 'json';
                    break;
                case 'association':
                    this._buildAssociationField(field, column, attributes, property, fields);
            }
        }

        this._handleSpecialComponents(column, attributes, field, property);
        this._finalizeAttributes(column, attributes, field, property);
        column.attributes = attributes;

        return column;
    }

    _buildAssociationField(field, column, attributes, property, fields) {
        const entity = field.entity;

        column.model = 'value';

        attributes.entity = entity;

        attributes.labelProperty = FormBuilderHelper.entityLabelProperty[entity] ?? 'name';

        if (field.relation === 'many_to_one') {
            const localField = field.localField;
            const required = fields[localField].flags.required;

            attributes.required = required;

            if (entity === 'media') {
                column.name = localField;
                column.tab = field.tab ?? 'general';
                column.card = field.card ?? 'media';
                column.componentName = 'sw-media-field';
            } else if (entity === 'cms_page' && property === 'cmsPage' && this.item.slotConfig !== undefined) {
                column.componentName = 'moorl-layout-card-v2';
            } else if (entity === 'user' || entity === `${this.entity}_media`) {
                return null;
            } else {
                column.name = localField;
                column.componentName = column.componentName ?? 'sw-entity-single-select';
                attributes.showClearableButton = required === undefined;
            }
        } else if (field.relation === 'many_to_many') {
            if (entity === 'category') {
                column.componentName = 'sw-category-tree-field';
                attributes.categoriesCollection = this.item[property];
            } else if (entity === 'property_group_option') {
                column.model = 'entityCollection';
                column.componentName = 'moorl-properties';
            } else {
                column.model = 'entityCollection';
                column.componentName = 'sw-entity-many-to-many-select';
                attributes.localMode = true;
            }
        } else if (entity === `${this.entity}_media`) {
            column.componentName = 'moorl-media-gallery';
            column.model = undefined;
            column.order = this.mediaOrder += 10;
            column.tab = 'general';
            column.card = 'media';
            attributes.item = this.item;
            attributes.entity = entity;
        } else if (column.componentName === undefined && field.relation === 'one_to_many') {
            column.model = 'entityCollection';
            column.componentName = 'sw-entity-multi-select';
            attributes.localMode = true;
        }
    }

    _handleSpecialComponents(column, attributes, field, property) {
        const refField = field.referenceField;
        const localField = field.localField;

        switch (column.componentName) {
            case 'moorl-layout-card-v2':
                column.card = null;
                column.model = undefined;
                attributes.item = this.item;
                attributes.entity = this.entity;
                break;

            case 'moorl-entity-grid':
            case 'moorl-entity-grid-v2':
                attributes.defaultItem = attributes.defaultItem ?? { [refField]: this.item[localField] };
                break;

            case 'moorl-entity-grid-card':
            case 'moorl-entity-grid-card-v2':
                column.card = null;
                column.model = undefined;
                attributes.title = column.label;
                attributes.componentName = this.componentName;
                attributes.defaultItem = attributes.defaultItem ?? { [refField]: this.item[localField] };
                break;

            case 'sw-seo-url':
                column.card = null;
                column.model = undefined;
                attributes.hasDefaultTemplate = false;
                attributes.urls = this.item[property];
                break;

            case 'sw-custom-field-set-renderer':
                column.model = undefined;
                attributes.entity = this.item;
                attributes.sets = this.customFieldSets;
                break;
        }
    }

    _finalizeAttributes(column, attributes, field, property) {
        attributes.label = column.label;
        attributes.required = attributes.required ?? field.flags.required;
        attributes.disabled = field.flags.write_protected;
        attributes.helpText = this.translationHelper.getLabel('helpText', property, false);
        attributes.componentName = attributes.componentName ?? column.componentName;

        if (this.item.translated?.[property] !== undefined) {
            attributes.placeholder = this.item.translated[property];
        }

        const parameters = {
            column,
            field,
            property,
            ...this.getInstanceParameters()
        };

        for (const [key, value] of Object.entries(attributes)) {
            if (typeof value === 'function') {
                attributes[key] = value(parameters);
            }
        }
    }

    getInstanceParameters() {
        return {
            entity: this.entity,
            componentName: this.componentName,
            item: this.item,
            currency: this.currency,
            tax: this.tax,
            customFieldSets: this.customFieldSets,
        }
    }

    _addColumnToStruct(column, property) {
        let tab = this.pageStruct.tabs.find(t => t.id === column.tab);
        if (!tab) {
            tab = {
                id: column.tab,
                label: this.translationHelper.getLabel('tab', column.tab),
                cards: []
            };
            this.pageStruct.tabs.push(tab);
        }

        const cardStandalone = !column.card;
        const cardId = cardStandalone ? property : column.card;
        const cardOrder = cardStandalone ? column.order : undefined;
        const cardLabel = cardStandalone ? undefined : this.translationHelper.getLabel('card', cardId);

        let card = tab.cards.find(c => c.id === cardId);
        if (!card) {
            card = {
                id: cardId,
                label: cardLabel,
                order: cardOrder,
                standalone: cardStandalone,
                fields: [],
            };
            tab.cards.push(card);
        }

        card.fields.push(column);
    }

    _sortStruct() {
        const getOrderIndex = (id) => {
            const index = this.order.indexOf(id);
            return index === -1 ? 9999 : index;
        };

        this.pageStruct.tabs.sort((a, b) => getOrderIndex(a.id) - getOrderIndex(b.id));

        this.pageStruct.tabs.forEach(tab => {
            tab.cards.sort((a, b) => {
                const aOrder = a.order ?? getOrderIndex(a.id);
                const bOrder = b.order ?? getOrderIndex(b.id);

                if (aOrder !== bOrder) return aOrder - bOrder;
                return a.id.localeCompare(b.id);
            });

            tab.cards.forEach(card => {
                card.fields.sort((a, b) => {
                    const aOrder = a.order ?? 9999;
                    const bOrder = b.order ?? 9999;

                    if (aOrder !== bOrder) return aOrder - bOrder;
                    return a.name.localeCompare(b.name);
                });
            });
        });
    }
}
