import template from './index.html.twig';

Shopware.Component.register('moorl-parameter-grid', {
    template,

    emits: ['update:parameters'],

    props: {
        label: {
            type: String,
            default: null,
            required: false,
        },
        helpText: {
            type: String,
            default: null,
            required: false,
        },
        parameters: {
            type: Array,
            required: true,
        },
        dataSelection: {
            type: Array,
            default: [],
            required: true,
        },
        fixed: {
            type: Boolean,
            default: true,
            required: false,
        },
        showActions: {
            type: Boolean,
            default: true,
            required: false,
        },
    },

    data() {
        return {
            items: this.parameters,
        };
    },

    computed: {
        gridColumns() {
            const columns = [
                {
                    label: this.$tc('moorl-parameter-grid.properties.name'),
                    property: 'name',
                    dataIndex: 'name',
                    primary: true,
                },
            ];

            if (this.fixed) {
                columns.push({
                    label: this.$tc('moorl-parameter-grid.properties.label'),
                    property: 'label',
                    dataIndex: 'label',
                    primary: true,
                });
            }

            columns.push({
                label: this.$tc('moorl-parameter-grid.properties.data'),
                property: 'data',
                dataIndex: 'data',
                primary: true,
                width: '320px',
            });

            return columns;
        },
    },

    methods: {
        toggleCustomValue(item, itemIndex) {
            this.items[itemIndex] = { ...item, isCustom: !item.isCustom };
            this.$emit('update:parameters', this.items);
        },

        onChangeItem(item, itemIndex) {
            if (
                !item.name ||
                !item.data ||
                itemIndex !== this.items.length - 1
            ) {
                return;
            }
            this.$emit('update:parameters', this.items);
        },

        addItem() {
            this.items.push({ data: '', name: '' });
            this.$emit('update:parameters', this.items);
        },

        deleteItem(itemIndex) {
            this.items.splice(itemIndex, 1);
            this.$emit('update:parameters', this.items);
        },
    },
});
