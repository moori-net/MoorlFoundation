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
    name: {tab: 'general', card: 'general',},
    teaser: {tab: 'general', card: 'general',},
    description: {tab: 'general', card: 'general'},
    descriptionHtml: {tab: 'general', card: 'general'},
    keywords: {tab: 'general', card: 'general',},
    type: {tab: 'general', card: 'general',},

    seoUrls: {tab: 'seo', componentName: 'sw-seo-url'},
    cmsPage: {tab: 'cmsPage', card: 'cmsPage',},

    active: {tab: 'general', card: 'visibility',},
    priority: {tab: 'general', card: 'visibility',},
    category: {tab: 'general', card: 'visibility',},
    categories: {tab: 'general', card: 'visibility',},
    salesChannel: {tab: 'general', card: 'visibility',},
    salesChannels: {tab: 'general', card: 'visibility',},

    salutation: {tab: 'general', card: 'contact', labelProperty: 'displayName',},
    title: {tab: 'general', card: 'contact',},
    firstName: {tab: 'general', card: 'contact',},
    lastName: {tab: 'general', card: 'contact',},
    email: {tab: 'general', card: 'contact',},
    phoneNumber: {tab: 'general', card: 'contact',},
    shopUrl: {tab: 'general', card: 'contact',},
    merchantUrl: {tab: 'general', card: 'contact',},

    street: {tab: 'address', card: 'general',},
    streetNumber: {tab: 'address', card: 'general',},
    zipcode: {tab: 'address', card: 'general',},
    city: {tab: 'address', card: 'general',},
    countryCode: {tab: 'address', card: 'general',},
    country: {tab: 'address', card: 'general',},
    countryState: {tab: 'address', card: 'general',},
    additionalAddressLine1: {tab: 'address', card: 'general',},
    additionalAddressLine2: {tab: 'address', card: 'general',},
    locationPlaceId: {tab: 'address', card: 'general',},

    company: {tab: 'general', card: 'company',},
    department: {tab: 'general', card: 'company',},
    executiveDirector: {tab: 'general', card: 'company',},
    placeOfFulfillment: {tab: 'general', card: 'company',},
    placeOfJurisdiction: {tab: 'general', card: 'company',},
    bankBic: {tab: 'general', card: 'company',},
    bankIban: {tab: 'general', card: 'company',},
    bankName: {tab: 'general', card: 'company',},
    taxOffice: {tab: 'general', card: 'company',},
    taxNumber: {tab: 'general', card: 'company',},
    vatId: {tab: 'general', card: 'company',},

    locationLat: {tab: 'address', card: 'location',},
    locationLon: {tab: 'address', card: 'location',},
    autoLocation: {tab: 'address', card: 'location',},
    marker: {tab: 'address', card: 'location',},

    timeZone: {tab: 'time', card: 'general',},
    openingHours: {tab: 'time', card: 'general',},
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

                        column.attrs.labelProperty = field.flags.moorl_label_property ?? 'name';

                        console.log('sw-entity-many-to-many-select');
                        console.log(column);
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
                column.attrs.componentName = column.componentName;

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
