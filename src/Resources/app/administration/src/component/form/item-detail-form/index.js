import template from './index.html.twig';
import './index.scss';

const { get, set } = Shopware.Utils.object;

Shopware.Component.register('moorl-item-detail-form', {
    template,

    mixins: [Shopware.Mixin.getByName('moorl-form')],

    computed: {
        formBuilderHelper() {
            return new MoorlFoundation.FormBuilderHelper({
                item: this.item,
                entity: this.entity,
                tc: this.$tc,
                componentName: this.componentName
            });
        },

        fieldModels() {
            return new Proxy({}, {
                get: (_, prop) => {
                    const path = String(prop);
                    if (get(this.item.extensions, path) !== undefined) {
                        return get(this.item.extensions, path);
                    }
                    return get(this.item, path);
                },

                set: (_, prop, value) => {
                    const path = String(prop);
                    if (get(this.item.extensions, path) !== undefined) {
                        set(this.item.extensions, path, value);
                    } else {
                        set(this.item, path, value);
                    }
                    return true;
                }
            });
        }
    },

    methods: {
        fieldAttributes(field) {
            return {
                ...field.attributes,
                disabled: this.isDisabled(field)
            };
        }
    }
});
