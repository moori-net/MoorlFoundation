const isType = (...types) => ({ field }) => types.includes(field.type);

const isEntity = (...entities) => ({ field }) => entities.includes(field.entity);

const isRegisteredEntity = () => ({ field }) => {
    const pluginConfig = MoorlFoundation.ModuleHelper.getByEntity(field.entity);
    return pluginConfig?.properties?.length > 0;
};

const autoConfiguration = [
    // general stuff
    {
        alias: 'hasComponentName',
        conditions: [({ column }) => column.componentName !== undefined]
    },
    {
        alias: 'hasTab',
        conditions: [({ column }) => column.tab !== undefined]
    },
    {
        alias: 'hasCard',
        conditions: [({ column }) => column.card !== undefined]
    },
    {
        alias: 'isMeteorComponent',
        conditions: [
            'hasComponentName',
            ({ column }) => column.componentName.startsWith('mt-')
        ]
    },
    {
        alias: 'isTranslated',
        conditions: [
            ({ field }) => field.flags?.translatable
        ]
    },
    // field type stuff
    {
        alias: 'isObject',
        conditions: [
            isType('json_object', 'object', 'list')
        ]
    },
    {
        alias: 'isString',
        description: ({ property }) => `Add componentName 'mt-text-field' (${property})`,
        conditions: [
            isType('string')
        ],
        apply({ column }) {
            column.componentName = 'mt-text-field';
        }
    },
    {
        alias: 'isText',
        description: ({ property }) => `Add componentName 'mt-textarea' (${property})`,
        conditions: [
            isType('text')
        ],
        apply({ column }) {
            column.componentName = 'mt-textarea';
        }
    },
    {
        description: ({ property }) => `Add componentName 'mt-text-editor' (${property})`,
        conditions: [
            ({ field }) => field.type === 'html' || field.flags.allow_html !== undefined
        ],
        apply({ column }) {
            column.componentName = 'mt-text-editor';
        }
    },
    {
        description: ({ property }) => `Add componentName 'mt-colorpicker' (${property})`,
        conditions: [
            'isString',
            ({ property }) => property.toLowerCase().includes('color')
        ],
        apply({ column }) {
            column.componentName = 'mt-colorpicker';
        }
    },
    {
        description: ({ property }) => `Add componentName 'mt-switch' (${property})`,
        conditions: [
            isType('boolean')
        ],
        apply({ column }) {
            column.componentName = 'mt-switch';
        }
    },
    {
        alias: 'isNumber',
        description: ({ property }) => `Add componentName 'mt-number-field' (${property})`,
        conditions: [
            isType('int', 'float', 'number')
        ],
        apply({ column, attributes, field }) {
            column.componentName = 'mt-number-field';
            attributes.numberType = field.type;
        }
    },
    {
        description: ({ property }) => `Add digits (${property})`,
        conditions: [
            isType('float')
        ],
        apply({ attributes }) {
            attributes.digits = 8;
        }
    },
    {
        description: ({ property }) => `Add componentName 'mt-date-field' (${property})`,
        conditions: [
            isType('date')
        ],
        apply({ column }) {
            column.componentName = 'mt-date-field';
        }
    },
    {
        alias: 'isPriceProperty',
        conditions: [
            ({ property }) => property.toLowerCase().includes('price')
        ],
        apply({ column }) {
            column.tab = 'price';
        }
    },
    {
        description: ({ property }) => `Add componentName 'moorl-price-field' (${property})`,
        conditions: [
            '!hasComponentName',
            'isObject',
            'isPriceProperty'
        ],
        apply({ column, attributes }) {
            column.componentName = 'moorl-price-field';
            attributes.tax = ({ tax }) => tax;
            attributes.currency = ({ currency }) => currency;
        }
    },
    // association stuff
    {
        alias: 'isAssociation',
        description: ({ property }) => `Enrich association (${property})`,
        conditions: [
            isType('association')
        ],
        apply({ column, attributes, field }) {
            column.model = 'value';

            attributes.entity = field.entity;
            attributes.labelProperty = ({ field }) => MoorlFoundation.FormBuilderHelper.entityLabelProperty[field.entity] ?? 'name';
        }
    },
    {
        alias: 'setTabRelations',
        description: ({ property }) => `Set column tab 'relations' (${property})`,
        conditions: [
            'isAssociation',
            '!hasTab'
        ],
        apply({ column }) {
            column.tab = 'relations';
        }
    },
    {
        alias: 'isManyToOne',
        description: ({ property }) => `Enrich many_to_one (${property})`,
        conditions: [
            'isAssociation',
            ({ field }) => field.relation === 'many_to_one'
        ],
        apply({ column, attributes, field, fields }) {
            column.name = field.localField;

            attributes.required = fields?.[field.localField]?.flags?.required;
            attributes.showClearableButton = !!attributes.required;
        }
    },
    {
        alias: 'isOneToMany',
        conditions: [
            'isAssociation',
            ({ field }) => field.relation === 'one_to_many'
        ]
    },
    {
        alias: 'isManyToMany',
        conditions: [
            'isAssociation',
            ({ field }) => field.relation === 'many_to_many'
        ]
    },
    {
        description: ({ property }) => `Add componentName 'moorl-media-gallery' (${property})`,
        conditions: [
            'isAssociation',
            ({ field, entity }) => field.entity === `${entity}_media`
        ],
        apply({ column, attributes, field, item }) {
            column.componentName = 'moorl-media-gallery';
            column.model = undefined;
            column.tab = 'general';
            column.card = 'media';

            attributes.item = item;
        }
    },
    {
        description: ({ property }) => `Add componentName 'sw-media-field' (${property})`,
        conditions: [
            'isManyToOne',
            isEntity('media')
        ],
        apply({ column }) {
            column.componentName = 'sw-media-field';
        }
    },
    {
        description: ({ property }) => `Add componentName 'moorl-layout-card-v2' (${property})`,
        conditions: [
            'isManyToOne',
            isEntity('cms_page'),
            ({ property, item }) => property === 'cmsPage' && item?.slotConfig !== undefined
        ],
        apply({ column }) {
            column.componentName = 'moorl-layout-card-v2';
        }
    },
    {
        description: ({ property }) => `Add componentName 'sw-entity-single-select' (${property})`,
        conditions: [
            '!hasComponentName',
            'isManyToOne',
            !isEntity('media', 'cms_page', 'user')
        ],
        apply({ column }) {
            column.componentName = 'sw-entity-single-select';
        }
    },
    {
        description: ({ property }) => `Add componentName 'sw-category-tree-field' (${property})`,
        conditions: [
            'isManyToMany',
            isEntity('category')
        ],
        apply({ column, attributes, property, item }) {
            column.componentName = 'sw-category-tree-field';
            attributes.categoriesCollection = item?.[property];
        }
    },
    {
        description: ({ property }) => `Add componentName 'moorl-properties' (${property})`,
        conditions: [
            'isManyToMany',
            isEntity('property_group_option')
        ],
        apply({ column }) {
            column.model = 'entityCollection';
            column.componentName = 'moorl-properties';
        }
    },
    {
        description: ({ property }) => `Add componentName 'moorl-entity-grid-card-v2' (${property})`,
        conditions: [
            '!hasComponentName',
            'isOneToMany',
            isRegisteredEntity()
        ],
        apply({ column }) {
            column.componentName = 'moorl-entity-grid-card-v2';
        }
    },
    {
        description: ({ property }) => `Fallback for ManyToMany Association (${property})`,
        conditions: [
            '!hasComponentName',
            'isManyToMany'
        ],
        apply({ column, attributes, field }) {
            column.model = 'entityCollection';
            column.componentName = 'sw-entity-many-to-many-select';

            attributes.localMode = true;
        }
    },
    {
        description: ({ property }) => `Fallback for OneToMany Association (${property})`,
        conditions: [
            '!hasComponentName',
            'isOneToMany'
        ],
        apply({ column, attributes, field }) {
            column.model = 'entityCollection';
            column.componentName = 'sw-entity-multi-select';

            attributes.localMode = true;
        }
    }
];

export default autoConfiguration;
