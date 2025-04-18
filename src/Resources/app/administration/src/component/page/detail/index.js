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
    // Contact
    salutation: {order: 0, tab: 'general', card: 'contact', attributes: {labelProperty: 'displayName'}},
    salutationId: {order: 0},
    title: {order: 10, tab: 'general', card: 'contact'},
    firstName: {order: 20, tab: 'general', card: 'contact'},
    lastName: {order: 30, tab: 'general', card: 'contact'},
    phoneNumber: {order: 50, tab: 'general', card: 'contact',},
    shopUrl: {order: 60, tab: 'general', card: 'contact',},
    merchantUrl: {order: 70, tab: 'general', card: 'contact',},
    email: {order: 80, tab: 'general', card: 'contact',},

    name: {order: 90, tab: 'general', card: 'general',},
    teaser: {order: 100, tab: 'general', card: 'general',},
    description: {order: 110, tab: 'general', card: 'general'},
    descriptionHtml: {order: 120, tab: 'general', card: 'general'},
    keywords: {order: 130, tab: 'general', card: 'general',},
    type: {order: 140, tab: 'general', card: 'general',},
    // Seo / Meta
    metaTitle: {order: 150, tab: 'seo', card: 'general'},
    metaDescription: {order: 160, tab: 'seo', card: 'general'},
    metaKeywords: {order: 170, tab: 'seo', card: 'general'},
    seoUrls: {order: 180, tab: 'seo', componentName: 'sw-seo-url'},
    // CMS Page
    cmsPage: {order: 190, tab: 'cmsPage', card: 'cmsPage',},
    fieldConfig: {order: 200, tab: 'cmsPage', card: 'cmsPage',},
    // visibility
    active: {order: 210, tab: 'general', card: 'visibility',},
    priority: {order: 220, tab: 'general', card: 'visibility',},
    category: {order: 230, tab: 'general', card: 'visibility',},
    categories: {order: 240, tab: 'general', card: 'visibility',},
    salesChannel: {order: 250, tab: 'general', card: 'visibility',},
    salesChannels: {order: 260, tab: 'general', card: 'visibility',},
    // address
    street: {order: 270, tab: 'address', card: 'general',},
    streetNumber: {order: 280, tab: 'address', card: 'general',},
    zipcode: {order: 290, tab: 'address', card: 'general',},
    city: {order: 300, tab: 'address', card: 'general',},
    countryCode: {order: 310, tab: 'address', card: 'general',},
    country: {order: 320, tab: 'address', card: 'general',},
    countryState: {order: 330, tab: 'address', card: 'general',},
    additionalAddressLine1: {order: 340, tab: 'address', card: 'general',},
    additionalAddressLine2: {order: 350, tab: 'address', card: 'general',},
    locationPlaceId: {order: 360, tab: 'address', card: 'general',},
    // company
    company: {order: 370, tab: 'general', card: 'company',},
    department: {order: 380, tab: 'general', card: 'company',},
    executiveDirector: {order: 390, tab: 'general', card: 'company',},
    placeOfFulfillment: {order: 400, tab: 'general', card: 'company',},
    placeOfJurisdiction: {order: 410, tab: 'general', card: 'company',},
    bankBic: {order: 420, tab: 'general', card: 'company',},
    bankIban: {order: 430, tab: 'general', card: 'company',},
    bankName: {order: 440, tab: 'general', card: 'company',},
    taxOffice: {order: 450, tab: 'general', card: 'company',},
    taxNumber: {order: 460, tab: 'general', card: 'company',},
    vatId: {order: 470, tab: 'general', card: 'company',},
    // location
    locationLat: {order: 480, tab: 'address', card: 'location',},
    locationLon: {order: 490, tab: 'address', card: 'location',},
    autoLocation: {order: 500, tab: 'address', card: 'location',},
    marker: {order: 510, tab: 'address', card: 'location',},
    // time
    timeZone: {
        order: 520,
        tab: 'time',
        card: 'general',
        componentName: 'moorl-select-field',
        attributes: {
            set: 'timeZone'
        }
    },
    openingHours: {order: 530, tab: 'time', card: 'general', componentName: 'moorl-opening-hours'},
    showOpeningHours: {order: 540, tab: 'time', card: 'general'},
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
        }
    },

    data() {
        return {
            pageStruct: {
                tabs: []
            },
            snippetStruct: {}
        };
    },

    computed: {
        customMapping() {
            return Shopware.Store.get('moorlFoundationState').getCustomEntityMapping(this.entity) ?? {};
        }
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

                if (['json_object'].indexOf(field.type) !== -1) {
                    column.type = 'json';
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

                if (this.customMapping[property] !== undefined) {
                    Object.assign(column, this.customMapping[property]);
                    if (this.customMapping[property].attributes !== undefined) {
                        Object.assign(attributes, this.customMapping[property].attributes );
                    }
                }

                if (column.hidden !== undefined) {
                    continue;
                }

                if (column.componentName !== undefined) {
                    attributes.componentName = column.componentName;
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

            console.log("this.pageStruct");
            console.log(this.pageStruct);
            console.log("this.snippetStruct");
            console.log(this.snippetStruct);
        },

        addColumnToPageStruct(column) {
            // Tab suchen oder anlegen
            let tab = this.pageStruct.tabs.find(t => t.id === column.tab);
            if (!tab) {
                tab = {
                    id: column.tab,
                    label: this.getLabel('tab', column.tab),
                    cards: []
                };
                this.pageStruct.tabs.push(tab);
            }

            // Card suchen oder anlegen
            let card = tab.cards.find(c => c.id === column.card);
            if (!card) {
                card = {
                    id: column.card,
                    label: this.getLabel('card', column.card),
                    fields: []
                };
                tab.cards.push(card);
            }

            // Field hinzufügen
            card.fields.push(column);
        },

        sortPageStruct() {
            const mergedMapping = Object.assign({}, mapping, this.customMapping);

            const getOrderIndex = (id) => {
                const index = order.indexOf(id);
                return index === -1 ? 9999 : index;
            };

            // Tabs sortieren
            this.pageStruct.tabs.sort((a, b) => getOrderIndex(a.id) - getOrderIndex(b.id));

            this.pageStruct.tabs.forEach(tab => {
                // Cards sortieren
                tab.cards.sort((a, b) => getOrderIndex(a.id) - getOrderIndex(b.id));

                tab.cards.forEach(card => {
                    // Fields sortieren nach "order" aus mergedMapping
                    card.fields.sort((a, b) => {
                        const aCfg = mergedMapping[a.name];
                        const bCfg = mergedMapping[b.name];

                        if (aCfg?.order !== undefined && bCfg?.order !== undefined) {
                            return aCfg.order - bCfg.order;
                        }
                        if (aCfg?.order !== undefined) return -1;
                        if (bCfg?.order !== undefined) return 1;

                        return a.name.localeCompare(b.name);
                    });
                });
            });
        }
    }
});
