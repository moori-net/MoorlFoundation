import template from './index.html.twig';
const {get, set} = Shopware.Utils.object;

Shopware.Component.register('moorl-form-fields', {
    template,

    props: {
        item: { type: Object, required: true },
        fields: { type: Array, required: true },
        fieldModels: { type: Object, required: true },
        isVisibleComponent: { type: Function, required: true },
        getStyle: { type: Function, required: true },
        fieldAttributes: { type: Function, required: true },
        getError: { type: Function, required: true },
    },

    computed: {
        configModels() {
            return new Proxy({}, {
                get: (_, prop) => {
                    const path = String(prop);
                    return get(this.item, path);
                },
                set: (_, prop, value) => {
                    const path = String(prop);
                    set(this.item, path, value);
                    return true;
                }
            });
        },
    }
});
