import mapping from './form-builder/mapping.js';
import order from './form-builder/order.json';

export default class FormBuilderHelper {
    constructor({entity, item, componentName, tc, snippetSrc = 'moorl-foundation', customFieldSets = [] }) {
        this.entity = entity;
        this.item = item;
        this.componentName = componentName;
        this.snippetSrc = snippetSrc;
        this.customFieldSets = customFieldSets;

        this.mapping = mapping;
        this.order = order;
        this.pageStruct = { tabs: [] };
        this.mediaOrder = 4999;

        this.translationHelper = new MoorlFoundation.TranslationHelper({componentName, snippetSrc, tc});

        this._init();
    }

    buildFormStruct() {
        if (this.pageStruct.tabs.length > 0) {
            console.warn(`[${this.entity}][${this.componentName}] TODO: prevent calling buildFormStruct() multiple times`);
            return this.pageStruct;
        }

        const fields = Shopware.EntityDefinition.get(this.entity).properties;

        for (const [property, field] of Object.entries(fields)) {
            if (
                field.type === 'uuid' ||
                ['createdAt', 'updatedAt', 'translations'].includes(property) ||
                field.flags.runtime !== undefined ||
                field.flags.computed !== undefined
            ) continue;

            const column = this._buildColumn(field, property);
            if (!column) continue;

            this._addColumnToStruct(column);
        }

        this._sortStruct();
        return this.pageStruct;
    }

    _init() {
        const customMapping = Shopware.Store.get('moorlFoundationState').getCustomEntityMapping(this.entity) ?? {};

        let currentOrder = 0;
        for (const [key, config] of Object.entries(this.mapping)) {
            config.order = currentOrder;
            currentOrder += 10;
        }

        Object.assign(this.mapping, customMapping);
    }

    _buildColumn(field, property) {
        const column = {
            tab: 'undefined',
            card: 'undefined',
            name: property,
            model: 'value',
            label: this.translationHelper.getLabel('field', property),
            order: this.mapping[property]?.order ?? 9999
        };

        const attributes = {};

        if (this.mapping[property]) {
            Object.assign(column, this.mapping[property]);
            if (this.mapping[property].attributes) {
                Object.assign(attributes, this.mapping[property].attributes);
            }
        }

        if (column.hidden) return null;

        // Typbasierte Logik
        switch (field.type) {
            case 'string':
                column.type = 'text';
                attributes.type = 'text';
                break;
            case 'text':
                column.type = 'text';
                if (!field.flags.allow_html) {
                    column.type = 'textarea';
                    attributes.type = 'text';
                } else {
                    attributes.componentName = 'sw-text-editor';
                    attributes.type = 'text';
                }
                break;
            case 'int':
            case 'float':
                column.type = field.type;
                attributes.type = 'number';
                attributes.numberType = field.type;
                attributes.componentName = 'sw-number-field';
                break;
            case 'boolean':
            case 'bool':
                column.type = 'bool';
                attributes.bordered = true;
                break;
            case 'date':
                column.type = 'date';
                attributes.dateType = 'date';
                attributes.size = 'default';
                attributes.componentName = 'sw-datepicker';
                break;
            case 'json_object':
                if (!column.componentName) return null;
                column.type = 'json';
                break;
            case 'association':
                this._buildAssociationField(field, column, attributes);
        }

        this._handleSpecialComponents(column, attributes, field, property);
        this._finalizeAttributes(column, attributes, field, property);
        column.attributes = attributes;

        return column;
    }

    _buildAssociationField(field, column, attributes) {
        attributes.entity = field.entity;

        if (field.relation === 'many_to_one') {
            if (field.entity === 'media') {
                column.order = this.mediaOrder += 10;
                column.tab = 'general';
                column.card = 'media';
                column.name = field.localField;
                attributes.componentName = 'sw-media-field';
            } else if (field.entity === 'user' || field.entity.includes('media')) {
                return null;
            } else {
                column.name = field.localField;
                attributes.componentName = 'sw-entity-single-select';
                attributes.showClearableButton = field.flags.required === undefined;
            }
        } else if (field.relation === 'many_to_many') {
            column.model = 'entityCollection';
            column.componentName = 'sw-entity-many-to-many-select';
            attributes.localMode = true;
            if (field.entity === 'media') {
                attributes.labelProperty = 'fileName';
            }
        } else if (field.entity === `${this.entity}_media`) {
            column.componentName = 'moorl-media-gallery';
            column.model = undefined;
            column.order = this.mediaOrder += 10;
            column.tab = 'general';
            column.card = 'media';
            attributes.item = this.item;
            attributes.entity = this.entity;
        } else if (column.componentName === undefined && field.relation === 'one_to_many') {
            column.model = 'entityCollection';
            column.componentName = 'sw-entity-multi-select';
            attributes.localMode = true;
            if (field.entity === 'media') {
                attributes.labelProperty = 'fileName';
            }
        }
    }

    _handleSpecialComponents(column, attributes, field, property) {
        const refField = field.referenceField;
        const localField = field.localField;

        switch (column.componentName) {
            case 'moorl-layout-card-v2':
                column.card = 'self';
                column.model = undefined;
                attributes.item = this.item;
                attributes.entity = this.entity;
                break;

            case 'moorl-entity-grid':
            case 'moorl-entity-grid-v2':
                attributes.defaultItem = { [refField]: this.item[localField] };
                break;

            case 'moorl-entity-grid-card':
            case 'moorl-entity-grid-card-v2':
                column.card = 'self';
                column.model = undefined;
                attributes.title = column.label;
                attributes.componentName = this.componentName;
                attributes.defaultItem = { [refField]: this.item[localField] };
                break;

            case 'sw-seo-url':
                column.card = 'self';
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
        attributes.labelProperty = attributes.labelProperty ?? field.flags.moorl_label_property ?? 'name';
        attributes.required = field.flags.required;
        attributes.disabled = field.flags.write_protected;
        attributes.helpText = this.translationHelper.getLabel('helpText', property, false);
        attributes.componentName = attributes.componentName ?? column.componentName;

        if (this.item.translated?.[property] !== undefined) {
            attributes.placeholder = this.item.translated[property];
        }
    }

    _addColumnToStruct(column) {
        let tab = this.pageStruct.tabs.find(t => t.id === column.tab);
        if (!tab) {
            tab = {
                id: column.tab,
                label: this.translationHelper.getLabel('tab', column.tab),
                cards: []
            };
            this.pageStruct.tabs.push(tab);
        }

        let card = tab.cards.find(c => c.id === column.card);
        if (!card) {
            card = {
                id: column.card,
                label: this.translationHelper.getLabel('card', column.card),
                fields: []
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
            tab.cards.sort((a, b) => getOrderIndex(a.id) - getOrderIndex(b.id));

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
