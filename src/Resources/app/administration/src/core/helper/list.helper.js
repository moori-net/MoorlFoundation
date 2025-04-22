export default class ListHelper {
    constructor({componentName, entity, tc, minVisibility = 0}) {
        this.componentName = componentName;
        this.entity = entity;
        this.minVisibility = minVisibility;
        this.properties = [];
        this.tc = tc;

        this.columns = [];
        this.associations = [];
        this.mediaProperty = undefined;

        this.translationHelper = new MoorlFoundation.TranslationHelper({
            tc: this.tc,
            componentName: this.componentName
        });

        this.ready = this._init();
    }

    getSortBy() {
        return this.properties[0];
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
                const pluginConfig = Shopware.Store.get('moorlProxy').getByEntity(this.entity);
                if (!pluginConfig) {
                    if (trys++ > 50) {
                        console.error(`[${this.componentName}] Properties not loaded in time (${this.entity})`);
                        return reject(new Error('Timeout: PluginConfig not found'));
                    }

                    return setTimeout(retry, 50);
                }

                const properties = pluginConfig.properties ?? [];

                this.properties = properties
                    .filter(prop => prop.visibility >= this.minVisibility)
                    .map(prop => prop.name);

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
                const association = parts.join(".");
                if (this.associations.indexOf(association) === -1) {
                    this.associations.push(association);
                }
            }
        });
    }

    _initProperties() {
        const fields = Shopware.EntityDefinition.get(this.entity).properties;

        this.properties.forEach((property) => {
            const key = property.split(".")[0];

            if (fields[key] === undefined) {
                console.error(`Property ${property} of ${this.componentName} not found in ${this.entity}`);
                return;
            }

            const field = fields[key];

            const column = {
                property: property,
                dataIndex: property,
                label: this.translationHelper.getLabel('field', property),
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
                    column.routerLink = MoorlFoundation.RouteHelper.getRouterLinkByEntity(this.entity, 'detail');
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

            this.columns.push(column);
        });
    }
}
