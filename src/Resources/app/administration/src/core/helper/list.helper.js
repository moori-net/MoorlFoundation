class ListHelper {
    constructor({identifier, entity, properties = [], snippetSrc = 'moorl-foundation', routerLink = null}) {
        this._identifier = identifier;
        this._entity = entity;
        this._properties = properties;
        this._snippetSrc = snippetSrc;
        this._routerLink = routerLink;

        this._columns = [];
        this._associations = [];
        this._mediaProperty = undefined;

        this._init();
    }

    getColumns() {
        console.log("this._columns");
        console.log(this._columns);
        return this._columns;
    }

    getAssociations() {
        console.log("this._associations");
        console.log(this._associations);
        return this._associations;
    }

    getMediaProperty() {
        console.log("this._mediaProperty");
        console.log(this._mediaProperty);
        return this._mediaProperty;
    }

    _init() {
        const fields = Shopware.EntityDefinition.get(this._entity).properties;

        console.log("fields");
        console.log(fields);

        // Init media Property
        for (const [property, field] of Object.entries(fields)) {
            if (
                field.type === 'association' &&
                field.relation === 'many_to_one' &&
                field.entity === 'media'
            ) {
                this._mediaProperty = property;
                this._associations.push(property);
                break;
            }
        }

        this._properties.forEach((property) => {
            // Init associations for listing
            let parts = property.split(".");
            parts.pop();
            if (parts.length > 0) {
                const association = parts.join(".");
                if (this._associations.indexOf(association) === -1) {
                    this._associations.push(association);
                }
                return;
            }

            // Init columns for listing
            if (fields[property] === undefined) {
                console.error(`Property ${property} of ${this._identifier} not found in ${this._entity}`);
                return;
            }

            const field = fields[property];
            const column = {
                property: property,
                dataIndex: property,
                label: `${this._snippetSrc}.properties.${property}`,
                allowResize: true,
            };

            if (['string'].indexOf(field.type) !== -1) {
                column.inlineEdit = 'string';
                column.align = 'left';
                column.routerLink = this._routerLink;
            }

            if (['int', 'float'].indexOf(field.type) !== -1) {
                column.inlineEdit = 'number';
                column.align = 'right';
            }

            if (['boolean'].indexOf(field.type) !== -1) {
                column.inlineEdit = 'boolean';
                column.align = 'center';
            }

            this._columns.push(column);
        });
    }
}

export default ListHelper;
