class ListHelper {
    constructor({componentName, entity, tc, minVisibility = 0}) {
        this._componentName = componentName;
        this._entity = entity;
        this._minVisibility = minVisibility;
        this._properties = [];
        this._tc = tc;

        this._columns = [];
        this._associations = [];
        this._mediaProperty = undefined;

        this._translationHelper = new MoorlFoundation.TranslationHelper({
            tc: this._tc,
            componentName: this._componentName
        });

        this.ready = this._init();
    }

    getColumns() {
        return this._columns;
    }

    getAssociations() {
        return this._associations;
    }

    getMediaProperty() {
        return this._mediaProperty;
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
                const pluginConfig = Shopware.Store.get('moorlProxy').getByEntity(this._entity);
                if (!pluginConfig) {
                    if (trys++ > 50) {
                        console.error(`[${this._componentName}] Properties not loaded in time (${this._entity})`);
                        return reject(new Error('Timeout: PluginConfig not found'));
                    }

                    return setTimeout(retry, 50);
                }

                const properties = pluginConfig.properties ?? [];

                this._properties = properties
                    .filter(prop => prop.visibility >= this._minVisibility)
                    .map(prop => prop.name);

                resolve();
            };

            retry();
        });
    }

    _initMediaProperty() {
        const fields = Shopware.EntityDefinition.get(this._entity).properties;

        for (const [property, field] of Object.entries(fields)) {
            const isMediaAssociation =
                field.type === 'association' &&
                field.relation === 'many_to_one' &&
                (
                    field.entity === 'media' ||
                    field.entity === `${this._entity}_media`
                );

            if (!isMediaAssociation) {
                continue;
            }

            this._mediaProperty = property;

            const isDirectMedia = field.entity === 'media';
            this._associations.push(isDirectMedia ? property : `${property}.media`);

            return;
        }
    }

    _initAssociations() {
        this._properties.forEach((property) => {
            let parts = property.split(".");
            parts.pop();
            if (parts.length > 0) {
                const association = parts.join(".");
                if (this._associations.indexOf(association) === -1) {
                    this._associations.push(association);
                }
            }
        });
    }

    _initProperties() {
        const fields = Shopware.EntityDefinition.get(this._entity).properties;

        this._properties.forEach((property) => {
            const key = property.split(".")[0];

            if (fields[key] === undefined) {
                console.error(`Property ${property} of ${this._componentName} not found in ${this._entity}`);
                return;
            }

            const field = fields[key];

            const column = {
                property: property,
                dataIndex: property,
                label: this._translationHelper.getLabel('field', property),
                allowResize: true,
            };

            switch (field.type) {
                case 'association':
                    if (field.relation === 'many_to_one') {
                        column.routerLink = MoorlFoundation.RouteHelper.getRouterLinkByEntity(field.entity, 'detail');
                        column.routerLinkIdProperty = field.localField;
                    }
                    break;

                case 'string':
                    column.inlineEdit = 'string';
                    column.align = 'left';
                    column.routerLink = MoorlFoundation.RouteHelper.getRouterLinkByEntity(this._entity, 'detail');
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
            }

            this._columns.push(column);
        });
    }
}

export default ListHelper;
