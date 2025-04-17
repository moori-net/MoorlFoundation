class ItemHelper {
    constructor({identifier, entity, snippetSrc = 'moorl-foundation', routerLink = null}) {
        this._identifier = identifier;
        this._entity = entity;
        this._snippetSrc = snippetSrc;
        this._routerLink = routerLink;

        this._columns = [];
        this._associations = [];

        this._init();
    }

    getColumns() {
        return this._columns;
    }

    getAssociations() {
        return this._associations;
    }

    _init() {
        const fields = Shopware.EntityDefinition.get(this._entity).properties;

        if (this._mediaProperty) {
            this._associations.push(this._mediaProperty);
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

export default ItemHelper;
