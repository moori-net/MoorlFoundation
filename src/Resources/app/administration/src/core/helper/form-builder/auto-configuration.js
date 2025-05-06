const entityLabelProperty = {
    media: 'fileName',
    product: 'productNumber',
    salutation: 'displayName',
    customer: 'customerNumber',
    moorl_sorting: 'label'
};
const not = (fn) => (context) => !fn(context);
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
        description: ({ property, column }) => `The componentName is already set to '${column.componentName}' (${property})`,
        conditions: [
            ({ column }) => column.componentName !== undefined
        ]
    },
    {
        alias: 'hasTab',
        conditions: [
            ({ column }) => column.tab !== undefined
        ]
    },
    {
        alias: 'hasCard',
        conditions: [
            ({ column }) => column.card !== undefined
        ]
    },
    {
        alias: 'isTranslated',
        conditions: [
            ({ field }) => field.flags?.translatable
        ]
    },
    {
        alias: 'isRequired',
        conditions: [
            ({ field }) => field.flags?.required,
        ],
        apply({ attributes }) {
            attributes.required = true;
        }
    },
    {
        alias: 'isDisabled',
        conditions: [
            ({ field }) => field.flags?.write_protected,
        ],
        apply({ attributes }) {
            attributes.disabled = true;
        }
    },
    {
        alias: 'isObject',
        conditions: [
            isType('json_object', 'object', 'list')
        ],
        apply({ column }) {
            column.cols ??= 12;
        }
    },
    // early break
    {
        description: ({ property, column }) => `The field is no association and the component is already set to '${column.componentName}' (${property})`,
        conditions: [
            not(isType('association')),
            'hasComponentName',
        ],
        apply({ column }) {
            column.cols ??= 6;
        }
    },
    // mixed conditions - early break
    {
        description: ({ property }) => `Add component 'mt-text-editor' (${property})`,
        conditions: [
            ({ field }) => field.type === 'html' || (field.type === 'text' && field.flags.allow_html !== undefined)
        ],
        apply({ column }) {
            column.componentName = 'mt-text-editor';
        }
    },
    {
        description: ({ property }) => `Add component 'mt-colorpicker' (${property})`,
        conditions: [
            ({ field, property }) => field.type === 'color' || (field.type === 'string' && property.toLowerCase().includes('color'))
        ],
        apply({ column, attributes }) {
            column.componentName = 'mt-colorpicker';

            attributes.colorOutput = 'hex';
            attributes.zIndex = 1000;
        }
    },
    // field type stuff
    {
        alias: 'isString',
        description: ({ property }) => `Add component 'mt-text-field' (${property})`,
        conditions: [
            isType('string')
        ],
        apply({ column }) {
            column.cols ??= 6;
            column.componentName = 'mt-text-field';
        }
    },
    {
        alias: 'isText',
        description: ({ property }) => `Add component 'mt-textarea' (${property})`,
        conditions: [
            isType('text')
        ],
        apply({ column }) {
            column.componentName = 'mt-textarea';
        }
    },
    {
        description: ({ property }) => `Add component 'mt-switch' (${property})`,
        conditions: [
            isType('boolean')
        ],
        apply({ column, attributes }) {
            column.cols ??= 6;
            column.componentName = 'mt-switch';

            attributes.bordered = true;
        }
    },
    {
        alias: 'isNumber',
        description: ({ property }) => `Add component 'mt-number-field' (${property})`,
        conditions: [
            isType('int', 'float', 'number')
        ],
        apply({ column, attributes, field }) {
            column.cols ??= 6;
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
        description: ({ property }) => `Add component 'mt-datepicker' (${property})`,
        conditions: [
            isType('date')
        ],
        apply({ column, attributes }) {
            column.cols ??= 6;
            column.componentName = 'mt-datepicker';

            attributes.size = 'default';
        }
    },
    {
        alias: 'isPriceProperty',
        conditions: [
            ({ property }) => property.toLowerCase().includes('price')
        ],
        apply({ column }) {
            column.tab = 'price';
            column.card = 'price';
        }
    },
    {
        alias: 'isCustomProperty',
        conditions: [
            ({ property }) => property.toLowerCase().includes('custom'),
            ({ property }) => !property.toLowerCase().includes('customer'),
        ],
        apply({ column }) {
            column.tab = 'customFields';
            column.card = 'customFields';
        }
    },
    {
        description: ({ property }) => `Add component 'moorl-price-field' (${property})`,
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
    {
        description: ({ property }) => `Object without component can't be resolved (${property})`,
        conditions: [
            '!hasComponentName',
            'isObject',
        ],
        apply({ column }) {
            column.hidden = true;
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
            attributes.entity = field.entity;
            attributes.labelProperty = ({ field }) => entityLabelProperty[field.entity] ?? 'name';
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
            attributes.disabled = fields?.[field.localField]?.flags?.write_protected;
            attributes.showClearableButton = attributes.required === undefined;
        }
    },
    {
        description: ({ property }) => `Hide property, item is new (${property})`,
        conditions: [
            'isAssociation',
            '!isManyToOne',
            ({ item }) => item._isNew
        ],
        apply({ column }) {
            column.hidden = true;
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
        ],
        apply({ column }) {}
    },
    {
        description: ({ property }) => `Add component 'moorl-media-gallery' (${property})`,
        conditions: [
            'isOneToMany',
            ({ field, entity }) => field.entity === `${entity}_media`
        ],
        apply({ column, attributes, item }) {
            column.componentName = 'moorl-media-gallery';
            column.tab = 'general';
            column.card = 'media';

            attributes.item = item;
        }
    },
    {
        description: ({ property }) => `Hide cover from 'moorl-media-gallery' (${property})`,
        conditions: [
            ({ field, entity }) => field.entity === `${entity}_media`
        ],
        apply({ column }) {
            column.hidden = true;
        }
    },
    {
        description: ({ property }) => `Add component 'moorl-media-field' (${property})`,
        conditions: [
            'isManyToOne',
            isEntity('media')
        ],
        apply({ column, attributes, entity }) {
            column.componentName = 'moorl-media-field';
            column.tab = 'general';
            column.card = 'media';

            attributes.entity = entity;
        }
    },
    {
        description: ({ property }) => `Add component 'moorl-layout-card-v2' (${property})`,
        conditions: [
            'isManyToOne',
            isEntity('cms_page'),
            ({ property, item }) => property === 'cmsPage' && item?.slotConfig !== undefined
        ],
        apply({ column }) {
            column.componentName = 'moorl-layout-card-v2';
            column.tab = 'cmsPage';
        }
    },
    {
        description: ({ property }) => `Add component 'sw-entity-single-select' (${property})`,
        conditions: [
            '!hasComponentName',
            'isManyToOne',
            not(isEntity('media', 'user')),
        ],
        apply({ column }) {
            column.cols ??= 6;
            column.componentName = 'sw-entity-single-select';
        }
    },
    {
        description: ({ property }) => `Add component 'sw-category-tree-field' (${property})`,
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
        description: ({ property }) => `Add component 'moorl-properties' (${property})`,
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
        description: ({ property }) => `Add component 'moorl-entity-grid-v2' (${property})`,
        conditions: [
            '!hasComponentName',
            'hasCard',
            'isOneToMany',
            isRegisteredEntity()
        ],
        apply({ column }) {
            column.componentName = 'moorl-entity-grid-v2';
        }
    },
    {
        description: ({ property }) => `Add component 'moorl-entity-grid-card-v2' (${property})`,
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
