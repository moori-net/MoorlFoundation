import template from './index.html.twig';
import './index.scss';

const order = [
    'general', 'visibility', 'media', 'contact', 'address',
    'company', 'seo', 'cmsPage', 'time', 'stock', 'relations',
    'customFields', 'undefined'
];

const mapping = {
    // Contact
    salutation: {order: 0, tab: 'general', card: 'contact', attributes: {labelProperty: 'displayName'}},
    title: {order: 10, tab: 'general', card: 'contact'},
    firstName: {order: 20, tab: 'general', card: 'contact'},
    lastName: {order: 30, tab: 'general', card: 'contact'},
    phoneNumber: {order: 50, tab: 'general', card: 'contact',},
    shopUrl: {order: 60, tab: 'general', card: 'contact', componentName: 'sw-url-field'},
    merchantUrl: {order: 70, tab: 'general', card: 'contact', componentName: 'sw-url-field'},
    email: {order: 80, tab: 'general', card: 'contact', componentName: 'sw-email-field'},
    // things
    name: {order: 90, tab: 'general', card: 'general',},
    teaser: {order: 100, tab: 'general', card: 'general',},
    description: {order: 110, tab: 'general', card: 'general'},
    descriptionHtml: {order: 120, tab: 'general', card: 'general'},
    keywords: {order: 130, tab: 'general', card: 'general',},
    type: {order: 140, tab: 'general', card: 'general',},
    highlight: {order: 140, tab: 'general', card: 'general',},
    // Seo / Meta
    metaTitle: {order: 150, tab: 'seo', card: 'general'},
    metaDescription: {order: 160, tab: 'seo', card: 'general'},
    metaKeywords: {order: 170, tab: 'seo', card: 'general'},
    seoUrls: {order: 180, tab: 'seo', componentName: 'sw-seo-url'},
    // CMS Page
    cmsPage: {order: 190, tab: 'cmsPage', card: 'cmsPage', componentName: 'moorl-layout-card-v2'},
    // visibility
    active: {order: 210, tab: 'general', card: 'visibility',},
    priority: {order: 220, tab: 'general', card: 'visibility',},
    category: {order: 230, tab: 'general', card: 'visibility',},
    categories: {order: 240, tab: 'general', card: 'visibility',},
    salesChannel: {order: 250, tab: 'general', card: 'visibility',},
    salesChannels: {order: 260, tab: 'general', card: 'visibility',},
    customerGroup: {order: 261, tab: 'general', card: 'visibility'},
    customerGroups: {order: 262, tab: 'general', card: 'visibility'},
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
    timeZone: {order: 520, tab: 'time', card: 'general', componentName: 'moorl-select-field', attributes: {set: 'timeZone'}},
    openingHours: {order: 530, tab: 'time', card: 'general', componentName: 'moorl-opening-hours'},
    showOpeningHours: {order: 540, tab: 'time', card: 'general'},
    // relations
    productManufacturers: {order: 550, tab: 'relations', card: 'general'},
    tags: {order: 560, tab: 'relations', card: 'general'},
    // customFields
    customFields: {order: 570, tab: 'customFields', card: 'customFields', componentName: 'sw-custom-field-set-renderer'},
    custom1: {order: 580, tab: 'customFields', card: 'customFields'},
    custom2: {order: 590, tab: 'customFields', card: 'customFields'},
    custom3: {order: 600, tab: 'customFields', card: 'customFields'},
    custom4: {order: 610, tab: 'customFields', card: 'customFields'},
};

Shopware.Component.register('moorl-page-detail', {
    template,

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder')
    ],

    inject: [
        'customFieldDataProviderService',
    ],

    props: {
        entity: {
            type: String,
            required: true,
        },
        componentName: {
            type: String,
            required: true
        },
        item: {
            type: Object,
            required: true,
        }
    },

    data() {
        return {
            customFieldSets: null,
            pageStruct: {tabs: []}
        };
    },

    computed: {
        translationHelper() {
            return new MoorlFoundation.TranslationHelper({
                tc: this.$tc,
                componentName: this.componentName
            });
        },

        customMapping() {
            return Shopware.Store.get('moorlFoundationState').getCustomEntityMapping(this.entity) ?? {};
        },

        mergedMapping() {
            return Object.assign({}, mapping, this.customMapping);
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async loadCustomFieldSets() {
            if (this.item.customFields === undefined) {
                console.log("this.item.customFields === undefined");
                return Promise.resolve();
            }

            this.customFieldSets = await this.customFieldDataProviderService
                .getCustomFieldSets(this.entity);
            console.log("this.customFieldSets");
            console.log(this.customFieldSets);
        },

        async createdComponent() {
            await this.loadCustomFieldSets();

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
                    label: this.translationHelper.getLabel('field', property)
                };

                const attributes = {};

                if (this.mergedMapping[property] !== undefined) {
                    Object.assign(column, this.mergedMapping[property]);
                    if (this.mergedMapping[property].attributes !== undefined) {
                        Object.assign(attributes, this.mergedMapping[property].attributes );
                    }
                }

                if (column.hidden !== undefined) {
                    continue;
                }

                if (['json_object'].indexOf(field.type) !== -1) {
                    if (column.componentName === undefined) {
                        continue;
                    }

                    column.type = 'json';
                }

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
                    column.type = field.type;

                    attributes.type = 'number';
                    attributes.numberType = field.type;
                    attributes.componentName = 'sw-number-field';
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
                        attributes.localMode = true;
                    } else if (column.componentName === undefined) {
                        continue;
                    }
                }

                if (column.componentName !== undefined) {
                    attributes.componentName = column.componentName;
                }

                if (column.componentName === 'moorl-entity-grid') {
                    attributes.defaultItem = {};
                    attributes.defaultItem[field.referenceField] = this.item[field.localField];
                }

                if (column.componentName === 'moorl-layout-card-v2') {
                    column.card = 'self';
                    column.model = undefined;

                    attributes.item = this.item;
                    attributes.entity = this.entity;
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

                if (column.componentName === 'sw-custom-field-set-renderer') {
                    column.model = undefined;

                    attributes.entity = this.item;
                    attributes.sets = this.customFieldSets;
                }

                column.order = column.order ?? 9999;

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
            let tab = this.pageStruct.tabs.find(t => t.id === column.tab);
            if (!tab) {
                tab = {
                    id: column.tab,
                    label: this.translationHelper.getLabel('tab', column.tab),
                    cards: []
                };
                this.pageStruct.tabs.push(tab);
            }

            let card = tab.cards.find(c => c.id === column.card);
            if (!card) {
                card = {
                    id: column.card,
                    label: this.translationHelper.getLabel('card', column.card),
                    fields: []
                };
                tab.cards.push(card);
            }

            card.fields.push(column);
        },

        sortPageStruct() {
            const getOrderIndex = (id) => {
                const index = order.indexOf(id);
                return index === -1 ? 9999 : index;
            };

            this.pageStruct.tabs.sort((a, b) => getOrderIndex(a.id) - getOrderIndex(b.id));

            this.pageStruct.tabs.forEach(tab => {
                tab.cards.sort((a, b) => getOrderIndex(a.id) - getOrderIndex(b.id));

                tab.cards.forEach(card => {
                    card.fields.sort((a, b) => {
                        const aOrder = a.order ?? 9999;
                        const bOrder = b.order ?? 9999;

                        if (aOrder !== bOrder) return aOrder - bOrder;
                        return a.name.localeCompare(b.name);
                    });
                });
            });
        }
    }
});
