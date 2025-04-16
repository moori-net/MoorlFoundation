const {ShopwareError} = Shopware.Classes;
const {isEmpty} = Shopware.Utils.types;
const {snakeCase} = Shopware.Utils.string;

Shopware.Mixin.register('moorl-flow-action-helper', {
    computed: {
        triggerEvent() {
            return Store.get('swFlowState').triggerEvent;
        },

        dataSelection() {
            return this.getEntityProperty(this.triggerEvent.data);
        }
    },

    methods: {
        isExistData(item) {
            return this.dataSelection.find(data => item === data.value);
        },

        generateParams: function (params) {
            if (isEmpty(params)) {
                return [{
                    name: '',
                    data: '',
                }];
            }

            const result = Object.entries(params).map(([key, value]) => {
                const data = value.replace(/{{|}}/g, '');
                const isCustomData = !this.isExistData(data);

                return {
                    name: key,
                    data: isCustomData ? value : data,
                    isCustomData,
                };
            });

            return [...result, {data: '', name: ''}];
        },

        convertParams(data) {
            const query = {};

            data.forEach(item => {
                if (!item.name) {
                    return;
                }

                if (item.isCustomData) {
                    query[item.name] = item.data;
                } else {
                    query[item.name] = item.data ? `{{${item.data}}}` : '';
                }
            });

            return query;
        },

        getEntityProperty(data) {
            const entities = [];

            Object.keys(data).forEach(key => {
                if (data[key].type === 'entity') {
                    entities.push(key);
                }
            });

            return entities.reduce((result, entity) => {
                const entityName = this.convertCamelCaseToSnakeCase(entity);
                const properties = Shopware.EntityDefinition.get(entityName).filterProperties(property => {
                    return Shopware.EntityDefinition.getScalarTypes().includes(property.type);
                });

                return result.concat(Object.keys(properties).map(property => {
                    return {
                        value: `${entity}.${property}`,
                        label: `${entity}.${property}`,
                    };
                }));
            }, []);
        },

        convertCamelCaseToSnakeCase(camelCaseText) {
            return snakeCase(camelCaseText);
        }
    },
});
