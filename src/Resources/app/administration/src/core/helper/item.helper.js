class ItemHelper {
    constructor({componentName, entity}) {
        this._componentName = componentName;
        this._entity = entity;

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

    hasSeoUrls() {
        return this._associations.indexOf("seoUrls") !== -1;
    }

    _init() {
        const fields = Shopware.EntityDefinition.get(this._entity).properties;

        for (const [property, field] of Object.entries(fields)) {
            if (field.type === 'association' && field.relation !== 'many_to_one') {
                this._associations.push(property);
            }
        }
    }
}

export default ItemHelper;
