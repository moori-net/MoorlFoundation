import template from './index.html.twig';
import './index.scss';

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
                    return this.item.extensions?.[prop] ?? this.item?.[prop];
                },
                set: (_, prop, value) => {
                    if (this.item.extensions?.hasOwnProperty(prop)) {
                        this.item.extensions[prop] = value;
                    } else {
                        this.item[prop] = value;
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
