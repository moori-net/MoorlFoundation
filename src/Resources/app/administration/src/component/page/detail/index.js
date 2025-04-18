import template from './index.html.twig';

const order = [
    'general', // first
    'contact',
    'address',
    'company',
    'seo',
    'cmsPage',
    'stock',
    'undefined', // last
];

const mapping = {
    active: {tab: 'general', card: 'visibility',},
    name: {tab: 'general', card: 'general',},
    description: {tab: 'general', card: 'general'},
    keywords: {tab: 'general', card: 'general',},
    seoUrls: {
        tab: 'seo',
        componentName: 'sw-seo-url',
    },
    cmsPage: {tab: 'cmsPage', card: 'cmsPage',},
    category: {tab: 'general', card: 'visibility',},
    categories: {tab: 'general', card: 'visibility',},
    salesChannel: {tab: 'general', card: 'visibility',},
    salesChannels: {tab: 'general', card: 'visibility',},
    salutation: {tab: 'general', card: 'contact', labelProperty: 'displayName',}
};

const customMapping = {
    merchantAreas: {
        tab: 'relations',
        componentName: 'moorl-entity-grid-card',
        filterColumns: [
            'zipcode',
            'deliveryTime',
            'deliveryPrice',
            'minOrderValue',
            'merchant.name'
        ]
    }
};

Shopware.Component.register('moorl-page-detail', {
    template,

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder')
    ],

    props: {
        entity: {
            type: String,
            required: true,
        },
        snippetSrc: {
            type: String,
            required: true,
            default: 'moorl-foundation'
        },
        item: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            pageStruct: {}
        };
    },

    computed: {
        cards() {}
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            const fields = Shopware.EntityDefinition.get(this.entity).properties;

            console.log(fields);

            for (const [property, field] of Object.entries(fields)) {
                if (
                    field.type === 'uuid' ||
                    ['createdAt', 'updatedAt', 'translations'].indexOf(property) !== -1 ||
                    field.flags.runtime !== undefined
                ) {
                    continue;
                }

                const column = {
                    name: property,
                    label: this.$tc(`${this.snippetSrc}.field.${property}`),
                    tab: 'undefined',
                    card: 'undefined',
                    model: 'value', // v-model value or entityCollection
                    componentName: undefined,
                    attrs: {}
                };

                if (['string'].indexOf(field.type) !== -1) {
                    column.type = 'text';
                }

                if (['text'].indexOf(field.type) !== -1) {
                    if (field.flags.allow_html === undefined) {
                        column.type = 'textarea';
                    } else {
                        column.componentName = 'sw-text-editor';
                    }
                }

                if (['int', 'float'].indexOf(field.type) !== -1) {
                    column.type = 'number';
                }

                if (['boolean', 'bool'].indexOf(field.type) !== -1) {
                    column.type = 'bool';
                    column.bordered = true;
                }

                if (field.type === 'association') {
                    column.attrs.entity = field.entity;

                    if (field.relation === 'many_to_one') {
                        if (field.entity === 'media') {
                            column.tab = 'general';
                            column.card = 'media';

                            column.name = field.localField;
                            column.componentName = 'sw-media-field';
                        } else {
                            column.name = field.localField;
                            column.componentName = 'sw-entity-single-select';

                            column.attrs.showClearableButton = field.flags.required === undefined;
                        }
                    } else if (field.relation === 'many_to_many') {
                        column.model = 'entityCollection';
                        column.componentName = 'sw-entity-many-to-many-select';

                        column.attrs.entity = field.entity;
                        column.attrs.labelProperty = field.flags.moorl_label_property ?? 'name';
                    }
                }

                if (property.includes("meta")) {
                    column.tab = 'seo';
                    column.card = 'general';
                }

                if (mapping[property] !== undefined) {
                    Object.assign(column, mapping[property]);
                }

                if (customMapping[property]) {
                    Object.assign(column, customMapping[property]);
                }

                if (column.componentName === 'moorl-entity-grid-card') {
                    column.card = 'self';
                    column.model = undefined;
                    column.attrs.title = column.label;
                    column.attrs.filterColumns = column.filterColumns;
                    column.attrs.defaultItem = {};
                    column.attrs.defaultItem[field.referenceField] = this.item[field.localField];
                }

                if (column.componentName === 'sw-seo-url') {
                    column.card = 'self';
                    column.model = undefined;
                    column.attrs.hasDefaultTemplate = false;
                    column.attrs.urls = this.item[property];
                }

                column.attrs.label = column.label;

                this.addColumnToPageStruct(column);
            }
        },

        addColumnToPageStruct(column) {
            if (this.pageStruct[column.tab] === undefined) {
                this.pageStruct[column.tab] = {
                    name: this.$tc(`${this.snippetSrc}.tab.${column.tab}`),
                    conditions: [],
                    cards: {}
                };
            }

            if (this.pageStruct[column.tab].cards[column.card] === undefined) {
                this.pageStruct[column.tab].cards[column.card] = {
                    name: this.$tc(`${this.snippetSrc}.card.${column.card}`),
                    conditions: [],
                    fields: {}
                };
            }

            this.pageStruct[column.tab].cards[column.card].fields[column.name] = column;
        }
    }
});
