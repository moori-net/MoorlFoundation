export default class ListHelper {
    constructor({
                    componentName,
                    entity,
                    currencies = [],
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
        this.priceProperties = [];
        this.dateProperties = [];
        this.currencies = currencies;

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
                        MoorlFoundation.Logger.error('ListHelper._loadProperties', this.componentName, {entity: this.entity});
                        return reject(new Error('Timeout: PluginConfig not found'));
                    }

                    return setTimeout(retry, 50);
                }

                const properties = pluginConfig.properties ?? [];

                this.properties = properties
                    .filter(prop => prop.visibility >= this.minVisibility)
                    .map(prop => prop.name);

                const highestVisibilityProp = properties.reduce((best, current) => {
                    return (!best || current.visibility > best.visibility) ? current : best;
                }, null);

                this.sortBy = highestVisibilityProp?.name ?? null;
                this.sortDirection = this.sortBy === 'autoIncrement' ? 'DESC' : 'ASC';

                this.pluginName = pluginConfig.pluginName ?? null;
                this.demoName = pluginConfig.demoName ?? 'standard';

                resolve();
            };

            retry();
        });
    }

    _initMediaProperty() {
        const fields = Shopware.EntityDefinition.get(this.entity).properties;

        for (const [property, field] of Object.entries(fields)) {
            const isMediaAssociation =
                field.type === 'association' &&
                field.relation === 'many_to_one' &&
                (
                    field.entity === 'media' ||
                    field.entity === `${this.entity}_media`
                );

            if (!isMediaAssociation) {
                continue;
            }

            this.mediaProperty = property;

            const isDirectMedia = field.entity === 'media';
            this.associations.push(isDirectMedia ? property : `${property}.media`);

            return;
        }
    }

    _initAssociations() {
        this.properties.forEach((property) => {
            let parts = property.split(".");
            parts.pop();
            if (parts.length > 0) {
                this._addAssociation(parts.join("."));
            }
        });
    }

    _addAssociation(association) {
        if (this.associations.indexOf(association) === -1) {
            this.associations.push(association);
        }
    }

    _initProperties(entity, properties, localField, parentPath = []) {
        entity = entity ?? this.entity;
        properties = properties ?? this.createNestedObject(this.properties);

        const fields = Shopware.EntityDefinition.get(entity).properties;

        for (const [key, childProperties] of Object.entries(properties)) {
            const propertyPath = [...parentPath, key];
            const property = propertyPath.join(".");

            if (fields[key] === undefined) {
                MoorlFoundation.Logger.error('ListHelper._initProperties', this.componentName, {key, entity});
                return;
            }

            const field = fields[key];

            const column = {
                property,
                dataIndex: property,
                label: this.translationHelper.getLabel('field', property),
                allowResize: true,
            };

            switch (field.type) {
                case 'association':
                    if (childProperties && field.relation === 'many_to_one') {
                        this._initProperties(
                            field.entity,
                            childProperties,
                            field.localField,
                            propertyPath
                        );
                        continue;
                    }
                    break;

                case 'string':
                    column.inlineEdit = 'string';
                    column.align = 'left';
                    column.routerLink = MoorlFoundation.RouteHelper.getRouterLinkByEntity(entity, 'detail');
                    column.routerLinkIdProperty = localField;
                    break;

                case 'int':
                case 'float':
                    column.inlineEdit = 'number';
                    column.align = 'right';
                    break;

                case 'boolean':
                    column.inlineEdit = 'boolean';
                    column.align = 'center';
                    break;

                case 'date':
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
            }

            this.columns.push(column);
        }
    }

    createNestedObject(keys) {
        const result = {};

        keys.forEach(key => {
            const parts = key.split('.');
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
        const props = [];

        for(const priceProperty of this.priceProperties) {
            for(const currency of this.currencies) {
                props.push({priceProperty, currency});
            }
        }

        return props;
    }

    _getCurrenciesColumns(property, column) {
        return this.currencies
            .toSorted((a, b) => {
                return b.isSystemDefault ? 1 : -1;
            })
            .map((item) => {
                return {
                    property: `${property}-${item.isoCode}`,
                    dataIndex: `${property}.${item.id}`,
                    label: column.label,
                    allowResize: true,
                    currencyId: item.id,
                    visible: item.isSystemDefault,
                    align: 'right',
                    useCustomSort: true,
                };
            });
    }
}
