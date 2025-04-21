import template from './index.html.twig';

Shopware.Component.register('moorl-modal-detail', {
    template,

    emits: ['cancel', 'save'],

    props: {
        entity: {
            type: String,
            required: true,
        },
        componentName: {
            type: String,
            required: true
        },
        item: {
            type: Object,
            required: true,
        }
    },

    computed: {
        itemName() {
            for (const property of ['name', 'label', 'key', 'technicalName']) {
                if (this.item[property] !== undefined) {
                    return this.item[property];
                }
            }

            return this.$tc('global.default.add');
        }
    }
});
