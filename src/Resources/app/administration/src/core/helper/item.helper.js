export default class ItemHelper {
    constructor({componentName, entity}) {
        this.componentName = componentName;
        this.entity = entity;
        this.associations = [];
        this.labelProperty = 'name';
        this._init();
    }

    getAssociations() {
        return this.associations;
    }

    getLabelProperty() {
        return this.labelProperty;
    }

    hasSeoUrls() {
        return this.associations.indexOf("seoUrls") !== -1;
    }

    _init() {
        const pluginConfig = MoorlFoundation.ModuleHelper.getByEntity(this.entity);

        this.labelProperty = pluginConfig.labelProperty ?? 'name';

        const fields = Shopware.EntityDefinition.get(this.entity).properties;

        for (const [property, field] of Object.entries(fields)) {
            if (field.type === 'association' && field.relation !== 'many_to_one') {
                if (field.entity === 'product') {
                    this.associations.push(`${property}.options.group`);
                    this.associations.push(`${property}.cover`);
                } else {
                    this.associations.push(property);
                }
            }
        }
    }
}
