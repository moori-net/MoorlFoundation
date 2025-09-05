export default class ListHelper {
    constructor({
                    componentName,
                    entity,
                    translationHelper,
                    currencies = [],
                    languages = [],
                    minVisibility = 0
    }) {
        this.componentName = componentName;
        this.entity = entity;
        this.minVisibility = minVisibility;
        this.pluginName = null;
        this.demoName = null;
        this.properties = [];
        this.sortBy = null;
        this.sortDirection = 'ASC';
        this.columns = [];
        this.associations = [];
        this.mediaProperty = undefined;
        this.languageProperty = undefined;
        this.priceProperties = [];
        this.dateProperties = [];
        this.currencies = currencies;
        this.languages = languages;
        this.translationHelper = translationHelper;
        this.allowInlineEdit = true;
        this.showSelection = true;

        this.entityOverride = null;
        this.ready = this._init();
    }

    getSortBy() {
        return this.sortBy;
    }

    getColumns() {
        return this.columns;
    }

    getPreviewColumn() {
        const prop = this.properties.find(p => p.mediaProperty !== undefined);
        if (prop) {
            return prop.name;
        }

        if (!this.properties.some(p => p.name === 'name')) {
            return this.sortBy;
        }

        return 'name';
    }

    getAssociations() {
        return this.associations;
    }

    getMediaProperty() {
        return this.mediaProperty;
    }

    overrideItems(items) {
        if (this.entityOverride) {
            for (const [property, item] of Object.entries(items)) {
                if (typeof item !== 'object') {continue;}
                for (const [key, value] of Object.entries(this.entityOverride)) {
                    if (typeof value === 'function') {
                        item[key] = value({item});
                    } else {
                        item[key] = value;
                    }
                }
            }
        }

        for (const [property, item] of Object.entries(items)) {
            if (typeof item !== 'object') {continue;}
            this.properties.forEach((prop) => {
                if (prop.snippetPath) {
                    const snippet = `${prop.snippetPath}.${item[prop.name]}`;
                    item[prop.name] = this.translationHelper.$tc(snippet);
                }
            });
        }

        return items;
    }

    async _init() {
        await this._loadProperties();
        this._initMediaProperty();
        this._initAssociations();
        this._initProperties();
    }

    async _loadProperties() {
        let trys = 0;
        return new Promise((resolve, reject) => {
            const retry = async () => {
                const pluginConfig = MoorlFoundation.ModuleHelper.getByEntity(this.entity);
                if (!pluginConfig) {
                    if (trys++ > 50) {
                        console.error('[ListHelper]', this.componentName, { entity: this.entity });
                        return reject(new Error('Timeout: PluginConfig not found'));
                    }
                    return setTimeout(retry, 50);
                }

                const props = pluginConfig.properties ?? [];
                this.properties = props.filter(prop => prop.visibility >= this.minVisibility);

                const highest = this.properties.reduce((best, current) => {
                    return (!best || current.visibility > best.visibility) ? current : best;
                }, null);

                this.sortBy = highest?.name ?? null;
                this.sortDirection = this.sortBy === 'autoIncrement' ? 'DESC' : 'ASC';

                this.pluginName = pluginConfig.pluginName ?? null;
                this.demoName = pluginConfig.demoName ?? 'standard';

                // Inherit config to listing module
                this.allowInlineEdit = pluginConfig.componentConfig?.list?.allowInlineEdit ?? true;
                this.allowDelete = pluginConfig.componentConfig?.list?.allowDelete ?? true;
                this.allowCreate = pluginConfig.componentConfig?.list?.allowCreate ?? true;
                this.showSelection = pluginConfig.componentConfig?.list?.showSelection ?? true;

                // Override item props by condition? (detail and listing)
                this.entityOverride = pluginConfig.entityOverride ?? null;
                if (this.entityOverride || this.properties.find(p => p.snippetPath)) {
                    // Restrict inline edit if there are overrides or translated values
                    this.allowInlineEdit = false;
                }

                resolve();
            };
            retry();
        });
    }

    _initMediaProperty() {
        const prop = this.properties.find(p => p.mediaProperty !== undefined);
        if (prop) {
            this.mediaProperty = prop.mediaProperty;
            this.associations.push(this.mediaProperty);
            return;
        }

        // Fallback: Try to find a media property from entity definition
        const fields = Shopware.EntityDefinition.get(this.entity).properties;

        for (const [property, field] of Object.entries(fields)) {
            const isMediaAssociation =
                field.type === 'association' &&
                field.relation === 'many_to_one' &&
                (field.entity === 'media' || field.entity === `${this.entity}_media`);
            if (!isMediaAssociation) {
                continue;
            }

            this.mediaProperty = field.entity === 'media' ? property : `${property}.media`;
            this.associations.push(this.mediaProperty);
            return;
        }
    }

    _initAssociations() {
        this.properties.forEach(({ name }) => {
            const parts = name.split(".");
            parts.pop();
            if (parts.length > 0) {
                this._addAssociation(parts.join("."));
            }
        });
    }

    _addAssociation(association) {
        if (!this.associations.includes(association)) {
            this.associations.push(association);
        }
    }

    _initProperties(entity, properties, localField, parentPath = []) {
        entity = entity ?? this.entity;
        properties = properties ?? this.createNestedObject(this.properties);

        const nestedLabel = Object.entries(properties).length > 1 || parentPath.length === 0;
        const fields = Shopware.EntityDefinition.get(entity).properties;

        for (const [key, childProperties] of Object.entries(properties)) {
            const propertyPath = [...parentPath, key];
            const property = propertyPath.join(".");
            const field = fields[key];
            if (!field) continue;

            const column = {
                property,
                dataIndex: property,
                label: this.translationHelper.getLabel('field', nestedLabel ? property : parentPath.join(".")),
                allowResize: false,
            };

            switch (field.type) {
                case 'association':
                    this._addAssociation(property);
                    if (childProperties && field.relation === 'many_to_one') {
                        this._initProperties(field.entity, childProperties, field.localField, propertyPath);
                    }
                    continue;

                case 'string':
                case 'text':
                    if (parentPath.length === 0) {
                        column.inlineEdit = 'string';
                    }
                    column.width = '140px';
                    column.align = 'left';
                    column.routerLink = MoorlFoundation.RouteHelper.getRouterLinkByEntity(entity, 'detail');
                    column.routerLinkIdProperty = localField;
                    break;

                case 'int':
                case 'float':
                    if (parentPath.length === 0) {
                        column.inlineEdit = 'number';
                    }
                    column.width = '100px';
                    column.align = 'right';
                    break;

                case 'boolean':
                    column.width = '80px';
                    column.inlineEdit = 'boolean';
                    column.align = 'center';
                    column.sortable = false;
                    break;

                case 'date':
                    column.width = '120px';
                    column.align = 'right';
                    this.dateProperties.push(property);
                    break;

                case 'json_object':
                    if (property.toLowerCase().includes("price")) {
                        this.priceProperties.push(property);
                        this.columns.push(...this._getCurrenciesColumns(property, column));
                        this._addAssociation('tax');
                    }
                    continue;

                case 'json_list':
                    if (property === 'languageIds') {
                        this.languageProperty = property;
                        this.columns.push(...this._getLanguageColumns(property, column));
                    }
                    continue;
            }

            this.columns.push(column);
        }
    }

    createNestedObject(properties) {
        const result = {};
        properties.forEach(({ name }) => {
            const parts = name.split('.');
            let current = result;
            parts.forEach((part, index) => {
                if (!current[part]) {
                    current[part] = (index === parts.length - 1) ? null : {};
                }
                current = current[part];
            });
        });
        return result;
    }

    getCurrenciesAndPriceProperties() {
        return this.priceProperties.flatMap(priceProperty =>
            this.currencies.map(currency => ({ priceProperty, currency }))
        );
    }

    _getLanguageColumns() {
        return this.languages
            .toSorted((a, b) => b.id === Shopware.Context.api.languageId ? 1 : -1)
            .map(language => ({
                property: `languageIds-${language.id}`,
                dataIndex: `languageIds.${language.id}`,
                label: language.locale?.code,
                allowResize: false,
                languageId: language.id,
                width: '80px',
                align: 'center',
                inlineEdit: 'boolean',
                sortable: false
            }));
    }

    _getCurrenciesColumns(property, column) {
        return this.currencies
            .toSorted((a, b) => b.isSystemDefault ? 1 : -1)
            .map(item => ({
                property: `${property}-${item.isoCode}`,
                dataIndex: `${property}.${item.id}`,
                label: column.label,
                allowResize: false,
                currencyId: item.id,
                visible: item.isSystemDefault,
                width: '100px',
                align: 'right',
                sortable: false
            }));
    }
}
