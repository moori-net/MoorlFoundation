import mapping from './form-builder/mapping.js';
import order from './form-builder/order.json';
import {applyAutoConfiguration} from './util/auto-config.util';
import autoConfiguration from './form-builder/auto-configuration';
import componentConfiguration from './form-builder/component-configuration';
import {buildImportExportProfile} from './util/import-export-profile.util';
import CmsElementHelper from "./cms-element.helper";

const {merge, cloneDeep} = Shopware.Utils.object;

export default class FormBuilderHelper {
    constructor({
                    entity,
                    item,
                    componentName,
                    tc,
                    snippetSrc = 'moorl-foundation',
                    useTabs = undefined,
                    useCards = undefined,
                    useStruct = true,
                    masterMapping = undefined,
                    path = undefined,
                    defaultColumn = {},
                }) {
        this.entity = entity ?? componentName;
        this.item = item;
        this.componentName = componentName;
        this.snippetSrc = snippetSrc;
        this.path = path;
        this.useTabs = useTabs;
        this.useCards = useCards;
        this.useStruct = useStruct;
        this.defaultColumn = defaultColumn;

        this.order = order;
        this.pageStruct = {tabs: []};
        this.columns = [];

        this.masterMapping = masterMapping;
        this.currency = null;
        this.tax = null;
        this.customFieldSets = [];

        this.translationHelper = new MoorlFoundation.TranslationHelper({componentName, snippetSrc, tc});

        this.initialized = false;
    }

    buildFormStruct() {
        this._init();

        const fields = this.masterMapping ?? Shopware.EntityDefinition.get(this.entity).properties;

        this._build(fields);

        if (this.useTabs === undefined) {
            this.useTabs = Object.keys(this.columns).length >= 10;
        }

        for (const column of this.columns) {
            this._addColumnToStruct(column, column.property);
        }

        this._sortStruct();

        console.log(this.pageStruct);
        console.log(this.item);

        return this.pageStruct;
    }

    _build(fields) {
        this._init();

        if (this.pageStruct.tabs.length > 0) {
            console.warn(`[${this.entity}][${this.componentName}] TODO: prevent calling buildFormStruct() multiple times`);
            return this.pageStruct;
        }

        if (!this.masterMapping) {
            buildImportExportProfile(this.entity);
        }

        for (const [property, field] of Object.entries(fields)) {
            if (
                field.type === 'uuid' ||
                ['createdAt', 'updatedAt', 'translations'].includes(property) ||
                field.flags?.runtime || field.flags?.computed
            ) continue;

            const column = this._buildColumn(field, property, fields);
            if (!column) continue;

            this.columns.push(column);
        }

        return this.columns;
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
        const path = this.path ? `${this.path}.${property}` : property;

        // Init column
        const column = Object.assign({
            path,
            property,
            tab: undefined,
            card: undefined,
            cols: undefined,
            name: property, // Overridden if association field in autoConfiguration
            model: 'value', // Meteor components have no model, it will be removed in componentConfiguration
            order: this.mapping[property]?.order ?? 9999
        }, this.defaultColumn);

        const attributes = {};

        // Assign from custom mapping
        if (this.mapping[property]) {
            Object.assign(column, this.mapping[property]);
            if (this.mapping[property].attributes) {
                Object.assign(attributes, this.mapping[property].attributes);
            }
        }

        // the column is a json_object and has a mapping attribute, then add a fieldset.
        // This fieldset items behave like the colum, same tab, same card.
        // As the column is a json_object, the property to the model value is specified as a path: property = config.hasCookieConsent
        if (field.type === 'json_object' && column.mapping) {
            // TODO: Unbind enrichCmsElementMapping this to another util or helper class
            CmsElementHelper.enrichCmsElementMapping(column.mapping);

            const formBuilderHelper = new FormBuilderHelper({
                defaultColumn: {
                    tab: column.tab,
                    card: column.card
                },
                item: this.item[property] ?? {},
                masterMapping: column.mapping,
                tc: this.tc,
                path
            });

            formBuilderHelper.translationHelper = this.translationHelper;

            const columns = formBuilderHelper._build(column.mapping);
            this.columns.push(...columns);

            return null;
        }

        // Early return if column should not be displayed
        if (column.hidden) return null;

        if (field.value !== undefined && this.item[property] === undefined) {
            this.item[property] = field.value;
        }

        // Run auto configuration
        const context = {
            column,
            attributes,
            field,
            property,
            fields,
            ...this.getInstanceParameters()
        };

        applyAutoConfiguration({configList: autoConfiguration, context, debug: true});

        // Some properties are not available if the item is on creation
        if (column.hidden) return null;

        if (column.componentName === undefined) {
            console.warn(`[FormBuilderHelper] No component found for "${property}"...`);
            return null;
        }

        // Inherit properties from CMS element configuration
        for (const key of ['tab', 'card']) {
            if (field[key] !== undefined) {
                column[key] = field[key];
            }
        }

        // Initial label
        column.label = this.translationHelper.getLabel('field', property);

        // Resolve function-attributes
        for (const [key, value] of Object.entries(attributes)) {
            if (typeof value === 'function') {
                attributes[key] = value(context);
            }
        }

        // Final adjustments
        applyAutoConfiguration({configList: componentConfiguration, context, debug: true});

        this._finalizeAttributes(column, attributes, field, property);

        return column;
    }

    _finalizeAttributes(column, attributes, field, property) {
        attributes.label = column.label;
        attributes.helpText = this.translationHelper.getLabel('helpText', property, false);

        if (this.item.translated?.[property] !== undefined) {
            attributes.placeholder = this.item.translated[property];
        }

        // Use one tab for all columns (Disable tab view)
        if (!this.useTabs) {
            column.tab = 'general';
        }

        column.attributes = attributes;
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
