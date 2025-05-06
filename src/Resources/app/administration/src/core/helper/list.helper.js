export default class ListHelper {
    constructor({
                    componentName,
                    entity,
                    currencies = [],
                    languages = [],
                    tc,
                    minVisibility = 0
                }) {
        this.componentName = componentName;
        this.entity = entity;
        this.minVisibility = minVisibility;
        this.pluginName = null;
        this.demoName = null;
        this.properties = [];
        this.tc = tc;
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

        this.translationHelper = new MoorlFoundation.TranslationHelper({
            tc: this.tc,
            componentName: this.componentName
        });

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
                    if (parentPath.length === 0) column.inlineEdit = 'string';
                    column.width = '140px';
                    column.align = 'left';
                    column.routerLink = MoorlFoundation.RouteHelper.getRouterLinkByEntity(entity, 'detail');
                    column.routerLinkIdProperty = localField;
                    break;

                case 'int':
                case 'float':
                    if (parentPath.length === 0) column.inlineEdit = 'number';
                    column.width = '100px';
                    column.align = 'right';
                    break;

                case 'boolean':
                    column.width = '80px';
                    column.inlineEdit = 'boolean';
                    column.align = 'center';
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
                    if (property.toLowerCase().includes("language")) {
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

    _getLanguageColumns(property, column) {
        return this.languages
            .toSorted((a, b) => b.id === Shopware.Context.api.languageId ? 1 : -1)
            .map(item => ({
                property: `${property}-${item.locale.code}`,
                dataIndex: `${property}.${item.id}`,
                label: item.locale.code,
                allowResize: false,
                languageId: item.id,
                width: '80px',
                align: 'center',
                inlineEdit: 'boolean'
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
                useCustomSort: true,
            }));
    }
}
