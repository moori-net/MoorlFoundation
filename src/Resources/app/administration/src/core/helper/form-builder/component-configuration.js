const isComponent = (...components) => ({ column }) => components.includes(column.componentName);

const componentConfiguration = [
    {
        alias: 'isFallbackTab',
        conditions: [
            ({ column }) => column.tab === undefined
        ],
        apply({ column }) {
            column.tab = 'undefined';
        }
    },
    {
        alias: 'isRelationFallbackCard',
        description: ({ property }) => `Set column card 'relations' (${property})`,
        conditions: [
            ({ column }) => column.card === undefined,
            ({ field }) => field.type === 'association'
        ],
        apply({ column }) {
            column.card = 'relations';
        }
    },
    {
        alias: 'isFallbackCard',
        conditions: [
            ({ column }) => column.card === undefined
        ],
        apply({ column }) {
            column.card = 'undefined';
        }
    },
    {
        description: 'Handle Meteor component',
        conditions: [
            ({ column }) => column.componentName.startsWith('mt-')
        ],
        apply({ column }) {
            column.model = undefined;
        }
    },
    {
        alias: 'isCard',
        description: 'Handle standalone card component',
        conditions: [
            ({ column }) => column.componentName.includes('-card')
        ],
        apply({ column }) {
            column.card = undefined;
        }
    },
    {
        description: 'Component: sw-seo-url',
        conditions: [
            isComponent('sw-seo-url')
        ],
        apply({ column, attributes, item, property }) {
            column.card = undefined;
            column.model = undefined;

            attributes.hasDefaultTemplate = false;
            attributes.urls = item[property];
        }
    },
    {
        description: 'Component: sw-entity-multi-id-select',
        conditions: [
            isComponent('sw-entity-multi-id-select'),
            ({ attributes }) => attributes.repository === undefined && attributes.entity !== undefined
        ],
        apply({ attributes }) {
            attributes.repository = Shopware.Service('repositoryFactory').create(attributes.entity);
        }
    },
    {
        description: 'Component: sw-custom-field-set-renderer',
        conditions: [
            isComponent('sw-custom-field-set-renderer')
        ],
        apply({ column, attributes, item, customFieldSets }) {
            column.model = undefined;

            attributes.entity = item;
            attributes.sets = customFieldSets;
        }
    },
    {
        description: 'Component: moorl-layout-card-v2',
        conditions: [
            isComponent('moorl-layout-card-v2')
        ],
        apply({ column, attributes, item }) {
            column.model = undefined;

            attributes.slotConfig = item.slotConfig;
        }
    },
    {
        alias: 'isEntityGridComponent',
        conditions: [
            isComponent('moorl-entity-grid-v2', 'moorl-entity-grid-card-v2')
        ],
        apply({ attributes, field, item }) {
            attributes.defaultItem = attributes.defaultItem ?? {
                [field.referenceField]: item[field.localField]
            };
        }
    },
    {
        description: 'Component: moorl-entity-grid-card-v2',
        conditions: [
            isComponent('moorl-entity-grid-card-v2')
        ],
        apply({ column, attributes, componentName }) {
            column.model = undefined;

            attributes.componentName = componentName;
        }
    }
];

export default componentConfiguration;
