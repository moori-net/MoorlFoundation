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

    data() {
        return {
            showForm: true // If the modal is closed and the form is mounted, there will be an error
        };
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
    },

    methods: {
        onSave() {
            this.showForm = false;

            setTimeout(() => {
                this.$emit('save');
            }, 50);
        },

        onCancel() {
            this.showForm = false;

            setTimeout(() => {
                this.$emit('cancel');
            }, 50);
        }
    }
});
