export default class ItemHelper {
    constructor({componentName, entity}) {
        this.componentName = componentName;
        this.entity = entity;
        this.associations = [];
        this._init();
    }

    getAssociations() {
        return this.associations;
    }

    hasSeoUrls() {
        return this.associations.indexOf("seoUrls") !== -1;
    }

    _init() {
        const fields = Shopware.EntityDefinition.get(this.entity).properties;

        for (const [property, field] of Object.entries(fields)) {
            if (field.type === 'association' && field.relation !== 'many_to_one') {
                this.associations.push(property);
            }
        }
    }
}
