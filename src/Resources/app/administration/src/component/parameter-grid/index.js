import template from './index.html.twig';
const {Component} = Shopware;

Component.register('moorl-parameter-grid', {
    template,

    model: {
        prop: 'parameters',
        event: 'change',
    },

    props: {
        parameters: {
            type: Array,
            default: [],
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
            records: this.parameters,
        };
    },
    
    computed: {
        parameterColumns() {
            return [
                {
                    label: this.$tc('moorl-parameter-grid.properties.value'),
                    property: 'value',
                    dataIndex: 'value',
                    primary: true
                },
                {
                    label: this.$tc('moorl-parameter-grid.properties.label'),
                    property: 'label',
                    dataIndex: 'label',
                    primary: true
                },
                {
                    label: this.$tc('moorl-parameter-grid.properties.data'),
                    property: 'data',
                    dataIndex: 'data',
                    primary: true
                },
            ];
        },
    },

    watch: {
        parameters: {
            handler(value) {
                if (!value || !value.length) {
                    return;
                }

                this.records = value;
            },
        },
    },

    methods: {
        changeToCustomText(item, itemIndex) {
            this.$set(this.records, itemIndex, { ...item, isCustomData: !item.isCustomData });
            this.$emit('change', this.records);
        },

        onChangeItem(item, itemIndex) {
            if (!item.name || !item.data || itemIndex !== this.records.length - 1) {
                return;
            }

            this.records = [...this.records, { name: '', data: '' }];
            this.$emit('change', this.records);
        },

        deleteItem(itemIndex) {
            this.$delete(this.records, itemIndex);
            this.$emit('change', this.records);
        },

        disableDelete(itemIndex) {
            return this.fixed || itemIndex === this.records.length - 1;
        },
    },
});
