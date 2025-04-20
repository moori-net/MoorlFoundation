import template from './index.html.twig';
import './index.scss';

const order = [
    'general', 'visibility', 'media', 'contact', 'address', 'comments',
    'company', 'seo', 'cmsPage', 'time', 'stock', 'relations',
    'customFields', 'undefined'
];

const mapping = {
    // Contact
    salutation: {tab: 'general', card: 'contact', attributes: {labelProperty: 'displayName'}},
    title: {tab: 'general', card: 'contact'},
    firstName: {tab: 'general', card: 'contact'},
    lastName: {tab: 'general', card: 'contact'},
    email: {tab: 'general', card: 'contact', componentName: 'sw-email-field'},
    phoneNumber: {tab: 'general', card: 'contact'},
    shopUrl: {tab: 'general', card: 'contact', componentName: 'sw-url-field'},
    merchantUrl: {tab: 'general', card: 'contact', componentName: 'sw-url-field'},

    // profile
    creatorType: {tab: 'general', card: 'profile'},
    followers: {tab: 'general', card: 'profile', attributes: {labelProperty: 'customerNumber'}},
    role: {tab: 'general', card: 'profile'},
    link: {tab: 'general', card: 'profile', componentName: 'sw-url-field'},
    imprint: {tab: 'general', card: 'profile'},
    customer: {tab: 'general', card: 'profile'},

    // things
    autoIncrement: {tab: 'general', card: 'general'},
    date: {tab: 'general', card: 'general'},
    name: {tab: 'general', card: 'general'},
    teaser: {tab: 'general', card: 'general'},
    description: {tab: 'general', card: 'general'},
    content: {tab: 'general', card: 'general'},
    contentCmsPage: {tab: 'general', card: 'general'},
    descriptionHtml: {tab: 'general', card: 'general'},
    keywords: {tab: 'general', card: 'general'},
    type: {tab: 'general', card: 'general'},
    highlight: {tab: 'general', card: 'general'},
    creator: {tab: 'general', card: 'general'},

    // Seo / Meta
    schemaOrgType: {tab: 'seo', card: 'general'},
    schemaOrgProperty: {tab: 'seo', card: 'general'},
    metaTitle: {tab: 'seo', card: 'general'},
    metaDescription: {tab: 'seo', card: 'general'},
    metaKeywords: {tab: 'seo', card: 'general'},
    seoUrls: {tab: 'seo', componentName: 'sw-seo-url'},

    // CMS Page
    cmsPage: {tab: 'cmsPage', card: 'cmsPage', componentName: 'moorl-layout-card-v2'},

    // visibility
    active: {tab: 'general', card: 'visibility'},
    priority: {tab: 'general', card: 'visibility'},
    sticky: {tab: 'general', card: 'visibility'},
    invisible: {tab: 'general', card: 'visibility'},
    showFrom: {tab: 'general', card: 'visibility'},
    showUntil: {tab: 'general', card: 'visibility'},
    category: {tab: 'general', card: 'visibility'},
    categories: {tab: 'general', card: 'visibility'},
    swCategories: {tab: 'general', card: 'visibility'},
    salesChannel: {tab: 'general', card: 'visibility'},
    salesChannels: {tab: 'general', card: 'visibility'},
    customers: {tab: 'general', card: 'visibility', attributes: {labelProperty: 'customerNumber'}},
    customerGroup: {tab: 'general', card: 'visibility'},
    customerGroups: {tab: 'general', card: 'visibility'},

    // address
    street: {tab: 'address', card: 'general'},
    streetNumber: {tab: 'address', card: 'general'},
    zipcode: {tab: 'address', card: 'general'},
    city: {tab: 'address', card: 'general'},
    countryCode: {tab: 'address', card: 'general'},
    country: {tab: 'address', card: 'general'},
    countryState: {tab: 'address', card: 'general'},
    additionalAddressLine1: {tab: 'address', card: 'general'},
    additionalAddressLine2: {tab: 'address', card: 'general'},
    locationPlaceId: {tab: 'address', card: 'general'},

    // company
    company: {tab: 'general', card: 'company'},
    department: {tab: 'general', card: 'company'},
    executiveDirector: {tab: 'general', card: 'company'},
    placeOfFulfillment: {tab: 'general', card: 'company'},
    placeOfJurisdiction: {tab: 'general', card: 'company'},
    bankBic: {tab: 'general', card: 'company'},
    bankIban: {tab: 'general', card: 'company'},
    bankName: {tab: 'general', card: 'company'},
    taxOffice: {tab: 'general', card: 'company'},
    taxNumber: {tab: 'general', card: 'company'},
    vatId: {tab: 'general', card: 'company'},

    // location
    locationLat: {tab: 'address', card: 'location'},
    locationLon: {tab: 'address', card: 'location'},
    autoLocation: {tab: 'address', card: 'location'},
    marker: {tab: 'address', card: 'location'},

    // time
    timeZone: {tab: 'time', card: 'general', componentName: 'moorl-select-field', attributes: {set: 'timeZone'}},
    openingHours: {tab: 'time', card: 'general', componentName: 'moorl-opening-hours'},
    showOpeningHours: {tab: 'time', card: 'general'},

    // relations
    products: {tab: 'relations', card: 'general', attributes: {labelProperty: 'productNumber'}},
    productManufacturers: {tab: 'relations', card: 'general'},
    tags: {tab: 'relations', card: 'general'},
    medias: {tab: 'relations', card: 'general'},
    downloads: {tab: 'relations', card: 'general'},

    // customFields
    customFields: {tab: 'customFields', card: 'customFields', componentName: 'sw-custom-field-set-renderer'},
    custom1: {tab: 'customFields', card: 'customFields'},
    custom2: {tab: 'customFields', card: 'customFields'},
    custom3: {tab: 'customFields', card: 'customFields'},
    custom4: {tab: 'customFields', card: 'customFields'},

    // media
    bannerColor: {tab: 'general', card: 'media', componentName: 'sw-colorpicker'},

    // Comments
    enableComments: {tab: 'comments', card: 'general'}
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
            let currentOrder = 0;
            for (const [key, config] of Object.entries(mapping)) {
                config.order = currentOrder;
                currentOrder += 10;
            }

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
            let mediaOrder = 4999;
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

                if (['date'].indexOf(field.type) !== -1) {
                    column.type = field.type;

                    attributes.dateType = 'date';
                    attributes.size = 'default';
                    attributes.componentName = 'sw-datepicker';
                }

                if (field.type === 'association') {
                    attributes.entity = field.entity;

                    if (field.relation === 'many_to_one') {
                        if (field.entity === 'media') {
                            column.order = mediaOrder += 10;
                            column.tab = 'general';
                            column.card = 'media';
                            column.name = field.localField;

                            attributes.componentName = 'sw-media-field';
                        } else if (
                            field.entity === 'user' ||
                            field.entity.includes('media')
                        ) {
                            // z.B. cover
                            continue;
                        } else {
                            column.name = field.localField;

                            attributes.componentName = 'sw-entity-single-select';
                            attributes.showClearableButton = field.flags.required === undefined;
                        }
                    } else if (field.relation === 'many_to_many') {
                        if (field.entity === 'category') {
                            column.componentName = 'sw-category-tree-field';

                            attributes.categoriesCollection = this.item[property];
                        } else {
                            column.model = 'entityCollection';
                            column.componentName = 'sw-entity-many-to-many-select';

                            attributes.localMode = true;

                            if (field.entity === 'media') {
                                attributes.labelProperty = 'fileName';
                            }
                        }
                    } else if (field.entity === `${this.entity}_media`) {
                        column.componentName = 'moorl-media-gallery';
                        column.model = undefined;
                        column.order = mediaOrder += 10;
                        column.tab = 'general';
                        column.card = 'media';

                        attributes.item = this.item;
                        attributes.entity = this.entity;
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

                    attributes.componentName = this.componentName;
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
                attributes.labelProperty = attributes.labelProperty ?? field.flags.moorl_label_property ?? 'name';
                attributes.required = field.flags.required === undefined;
                attributes.disabled = field.flags.write_protected !== undefined;
                attributes.helpText = this.translationHelper.getLabel('helpText', property, false);

                if (this.item.translated && this.item.translated[property] !== undefined) {
                    attributes.placeholder = this.item.translated[property];
                }

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
