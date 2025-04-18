import template from './index.html.twig';

const order = [
    'general', // first
    'media',
    'contact',
    'address',
    'company',
    'seo',
    'cmsPage',
    'time',
    'stock',
    'relations',
    'undefined' // last
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

    salutation: {
        tab: 'general',
        card: 'contact',
        attributes: {
            labelProperty: 'displayName',
        }
    },
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

// Test
const customMapping = {
    merchantAreas: {
        tab: 'relations',
        componentName: 'moorl-entity-grid-card',
        attributes: {
            filterColumns: [
                'zipcode',
                'deliveryTime',
                'deliveryPrice',
                'minOrderValue',
                'merchant.name'
            ]
        }
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
        useTabs: {
            type: Boolean,
            required: false,
            default: true
        },
        customMapping: {
            type: Object,
            required: false,
            default: {}
        }
    },

    data() {
        return {
            pageStruct: {},
            snippetStruct: {}
        };
    },

    computed: {
        cards() {}
    },

    created() {
        this.createdComponent();
    },

    methods: {
        getLabel(group, property) {
            const snippetSets = [
                this.snippetSrc
            ];

            for (const set of snippetSets) {
                if (this.snippetStruct[set] === undefined) {
                    this.snippetStruct[set] = {};
                }
                if (this.snippetStruct[set][group] === undefined) {
                    this.snippetStruct[set][group] = {};
                }
                if (this.snippetStruct[set][group][property] === undefined) {
                    this.snippetStruct[set][group][property] = property;
                }

                const snippet = `${set}.${group}.${property}`;

                if (this.$tc(snippet) !== snippet) {
                    return this.$tc(snippet);
                }
            }

            return `${group}.${property}`;
        },

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
                    tab: 'undefined',
                    card: 'undefined',
                    name: property,
                    model: 'value',
                    label: this.getLabel('field', property)
                };

                const attributes = {};

                if (['string'].indexOf(field.type) !== -1) {
                    column.type = 'text';
                    attributes.type = 'text';
                }

                if (['text'].indexOf(field.type) !== -1) {
                    if (field.flags.allow_html === undefined) {
                        column.type = 'textarea';

                        attributes.type = 'text';
                    } else {
                        column.type = 'text';

                        attributes.componentName = 'sw-text-editor';
                        attributes.type = 'text';
                    }
                }

                if (['int', 'float'].indexOf(field.type) !== -1) {
                    column.type = 'number';
                    attributes.type = field.type;
                }

                if (['boolean', 'bool'].indexOf(field.type) !== -1) {
                    column.type = 'bool';
                    attributes.bordered = true;
                }

                if (field.type === 'association') {
                    attributes.entity = field.entity;

                    if (field.relation === 'many_to_one') {
                        if (field.entity === 'media') {
                            column.tab = 'general';
                            column.card = 'media';
                            column.name = field.localField;

                            attributes.componentName = 'sw-media-field';
                        } else {
                            column.name = field.localField;

                            attributes.componentName = 'sw-entity-single-select';
                            attributes.showClearableButton = field.flags.required === undefined;
                        }
                    } else if (field.relation === 'many_to_many') {
                        column.model = 'entityCollection';
                        column.componentName = 'sw-entity-many-to-many-select';

                        attributes.labelProperty = field.flags.moorl_label_property ?? 'name';
                    }
                }

                if (property.includes("meta")) {
                    column.tab = 'seo';
                    column.card = 'general';
                }

                if (mapping[property] !== undefined) {
                    Object.assign(column, mapping[property]);

                    if (mapping[property].attributes !== undefined) {
                        Object.assign(attributes, mapping[property].attributes );
                    }
                }

                if (customMapping[property] !== undefined) {
                    Object.assign(column, customMapping[property]);

                    if (customMapping[property].attributes !== undefined) {
                        Object.assign(attributes, customMapping[property].attributes );
                    }
                }

                if (column.componentName === 'moorl-entity-grid-card') {
                    column.card = 'self';
                    column.model = undefined;

                    attributes.title = column.label;
                    attributes.defaultItem = {};
                    attributes.defaultItem[field.referenceField] = this.item[field.localField];
                }

                if (column.componentName === 'sw-seo-url') {
                    column.card = 'self';
                    column.model = undefined;

                    attributes.hasDefaultTemplate = false;
                    attributes.urls = this.item[property];
                }

                attributes.label = column.label;

                Object.assign(column, {attributes});

                this.addColumnToPageStruct(column);
            }

            this.sortPageStruct();

            console.log(this.snippetStruct);
        },

        addColumnToPageStruct(column) {
            if (this.pageStruct[column.tab] === undefined) {
                this.pageStruct[column.tab] = {
                    label: this.getLabel('tab', column.tab),
                    conditions: [],
                    cards: {}
                };
            }

            if (this.pageStruct[column.tab].cards[column.card] === undefined) {
                this.pageStruct[column.tab].cards[column.card] = {
                    label: this.getLabel('card', column.card),
                    conditions: [],
                    fields: {}
                };
            }

            this.pageStruct[column.tab].cards[column.card].fields[column.name] = column;
        },

        sortPageStruct() {
            const sortedStruct = {};

            const tabOrder = [...order, ...Object.keys(this.pageStruct).filter(t => !order.includes(t))];

            const byOrder = (a, b) => {
                const aIndex = order.indexOf(a);
                const bIndex = order.indexOf(b);
                if (aIndex === -1 && bIndex === -1) return a.localeCompare(b);
                if (aIndex === -1) return 1;
                if (bIndex === -1) return -1;
                return aIndex - bIndex;
            };

            const sortFields = (fieldKeys, currentCard, currentTab) => {
                return fieldKeys.sort((a, b) => {
                    const aInMap = mapping[a];
                    const bInMap = mapping[b];

                    const aValid = aInMap && aInMap.card === currentCard && aInMap.tab === currentTab;
                    const bValid = bInMap && bInMap.card === currentCard && bInMap.tab === currentTab;

                    if (aValid && bValid) {
                        return Object.keys(mapping).indexOf(a) - Object.keys(mapping).indexOf(b);
                    }

                    return a.localeCompare(b);
                });
            };

            tabOrder.forEach(tabKey => {
                const tab = this.pageStruct[tabKey];
                if (!tab) return;

                const cardKeys = Object.keys(tab.cards).sort(byOrder);
                const sortedCards = {};

                cardKeys.forEach(cardKey => {
                    const card = tab.cards[cardKey];

                    const fieldKeys = Object.keys(card.fields);
                    const sortedFieldKeys = sortFields(fieldKeys, cardKey, tabKey);

                    const sortedFields = {};
                    sortedFieldKeys.forEach(fieldKey => {
                        sortedFields[fieldKey] = card.fields[fieldKey];
                    });

                    sortedCards[cardKey] = {
                        ...card,
                        fields: sortedFields
                    };
                });

                sortedStruct[tabKey] = {
                    ...tab,
                    cards: sortedCards
                };
            });

            this.pageStruct = sortedStruct;
        }
    }
});
