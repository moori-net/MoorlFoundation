const mapping = {
    // Other
    children: {tab: 'general', card: 'general', cols: 12},
    directory: {
        tab: 'directory',
        card: 'directory',
        componentName: 'moorl-file-explorer',
        conditions: [
            {property: 'clientId', value: null, operator: '!=='}
        ],
        cols: 12
    },

    // Contact
    salutation: {tab: 'general', card: 'contact', attributes: {labelProperty: 'displayName'}, cols: 2},
    title: {tab: 'general', card: 'contact', cols: 2},
    firstName: {tab: 'general', card: 'contact', cols: 4},
    lastName: {tab: 'general', card: 'contact', cols: 4},
    email: {tab: 'general', card: 'contact', componentName: 'sw-email-field', cols: 6},
    phoneNumber: {tab: 'general', card: 'contact', cols: 6},
    shopUrl: {tab: 'general', card: 'contact', componentName: 'sw-url-field', cols: 6},
    merchantUrl: {tab: 'general', card: 'contact', componentName: 'sw-url-field', cols: 6},

    // Profile
    creatorType: {tab: 'general', card: 'profile', cols: 12},
    followers: {tab: 'general', card: 'profile', attributes: {labelProperty: 'customerNumber'}, cols: 12},
    role: {tab: 'general', card: 'profile', cols: 12},
    link: {tab: 'general', card: 'profile', componentName: 'sw-url-field', cols: 12},
    imprint: {tab: 'general', card: 'profile', cols: 12},

    // Stock
    isStock: {tab: 'stock', card: 'general', cols: 12},
    stock: {tab: 'stock', card: 'general', cols: 12},
    sales: {tab: 'stock', card: 'general', cols: 12},
    availableStock: {tab: 'stock', card: 'general', cols: 12},
    customPrice: {tab: 'stock', card: 'general', cols: 12},
    deliveryTime: {tab: 'stock', card: 'general', cols: 12},
    tax: {tab: 'stock', card: 'general', cols: 12},

    // Things
    name: {tab: 'general', card: 'general', cols: 12},
    technicalName: {tab: 'general', card: 'general', cols: 6},
    highlight: {tab: 'general', card: 'general', cols: 6},
    type: {tab: 'general', card: 'general', cols: 6},
    levelCount: {tab: 'general', card: 'general', cols: 6},
    autoIncrement: {tab: 'general', card: 'general', cols: 6},
    date: {tab: 'general', card: 'general', cols: 6},
    optionPercentage: {tab: 'general', card: 'general', cols: 6},
    showDiscount: {tab: 'general', card: 'general', cols: 6},
    hasCodes: {tab: 'general', card: 'general', cols: 6},
    hasFiles: {tab: 'general', card: 'general', cols: 6},
    hasLinks: {tab: 'general', card: 'general', cols: 6},
    redeemCode: {tab: 'general', card: 'general', cols: 6},

    subscriptionTime: {tab: 'general', card: 'general', cols: 6},
    teaser: {tab: 'general', card: 'general', cols: 12},
    description: {tab: 'general', card: 'general', cols: 12},
    content: {tab: 'general', card: 'general', cols: 12},
    contentCmsPage: {tab: 'general', card: 'general', cols: 12},
    descriptionHtml: {tab: 'general', card: 'general', cols: 12},
    keywords: {tab: 'general', card: 'general', cols: 12},
    customerNumber: {tab: 'general', card: 'general', cols: 12},
    info: {tab: 'general', card: 'general', cols: 12},

    // SEO / Meta
    schemaOrgType: {tab: 'seo', card: 'general', cols: 12},
    schemaOrgProperty: {tab: 'seo', card: 'general', cols: 12},
    metaTitle: {tab: 'seo', card: 'general', cols: 12},
    metaDescription: {tab: 'seo', card: 'general', cols: 12},
    metaKeywords: {tab: 'seo', card: 'general', cols: 12},
    seoUrls: {tab: 'seo', componentName: 'sw-seo-url', cols: 12},

    // CMS Page
    cmsPage: {tab: 'cmsPage', card: 'cmsPage', cols: 12},

    // Visibility
    active: {tab: 'general', card: 'visibility', cols: 6},
    priority: {tab: 'general', card: 'visibility', cols: 6},
    sticky: {tab: 'general', card: 'visibility', cols: 6},
    invisible: {tab: 'general', card: 'visibility', cols: 6},
    showFrom: {tab: 'general', card: 'visibility', cols: 6},
    showUntil: {tab: 'general', card: 'visibility', cols: 6},
    categories: {tab: 'general', card: 'visibility', cols: 12},
    swCategories: {tab: 'general', card: 'visibility', cols: 12},
    shopwareCategories: {tab: 'general', card: 'visibility', cols: 12},
    salesChannels: {tab: 'general', card: 'visibility', cols: 12},
    customers: {tab: 'general', card: 'visibility', attributes: {labelProperty: 'customerNumber'}, cols: 12},
    customerGroups: {tab: 'general', card: 'visibility', cols: 12},
    redirectFix: {tab: 'general', card: 'visibility', cols: 12},

    // Address
    street: {tab: 'address', card: 'general', cols: 8},
    streetNumber: {tab: 'address', card: 'general', cols: 4},
    zipcode: {tab: 'address', card: 'general', cols: 4},
    city: {tab: 'address', card: 'general', cols: 8},
    countryCode: {tab: 'address', card: 'general', cols: 2},
    country: {tab: 'address', card: 'general', cols: 5},
    countryState: {tab: 'address', card: 'general', cols: 5},
    additionalAddressLine1: {tab: 'address', card: 'general', cols: 6},
    additionalAddressLine2: {tab: 'address', card: 'general', cols: 6},
    locationPlaceId: {tab: 'address', card: 'general', cols: 12},

    // Company
    company: {tab: 'general', card: 'company', cols: 12},
    department: {tab: 'general', card: 'company', cols: 6},
    executiveDirector: {tab: 'general', card: 'company', cols: 6},
    placeOfFulfillment: {tab: 'general', card: 'company', cols: 6},
    placeOfJurisdiction: {tab: 'general', card: 'company', cols: 6},
    bankBic: {tab: 'general', card: 'company', cols: 6},
    bankIban: {tab: 'general', card: 'company', cols: 6},
    bankName: {tab: 'general', card: 'company', cols: 6},
    taxOffice: {tab: 'general', card: 'company', cols: 6},
    taxNumber: {tab: 'general', card: 'company', cols: 6},
    vatId: {tab: 'general', card: 'company', cols: 6},

    // Location
    locationLat: {tab: 'address', card: 'location', cols: 6},
    locationLon: {tab: 'address', card: 'location', cols: 6},
    autoLocation: {tab: 'address', card: 'location', cols: 6},
    marker: {tab: 'address', card: 'location', cols: 6},

    // Time
    timeZone: {tab: 'time', card: 'general', componentName: 'moorl-select-field', attributes: {set: 'timeZone'}, cols: 6},
    showOpeningHours: {tab: 'time', card: 'general', cols: 6},
    openingHours: {
        tab: 'time',
        card: 'general',
        componentName: 'moorl-opening-hours',
        conditions: [
            {property: 'showOpeningHours', value: true, operator: 'eq'}
        ],
        cols: 12
    },

    // Relations
    products: {tab: 'relations', card: 'relations', attributes: {labelProperty: 'productNumber'}, cols: 12},
    productManufacturers: {tab: 'relations', card: 'relations', cols: 12},
    tags: {tab: 'relations', card: 'relations', cols: 12},
    medias: {tab: 'relations', card: 'relations', cols: 12},
    downloads: {tab: 'relations', card: 'relations', cols: 12},

    salesChannel: {tab: 'relations', card: 'relations', cols: 12},
    customer: {tab: 'relations', card: 'relations', attributes: {labelProperty: 'customerNumber'}, cols: 12},
    customerGroup: {tab: 'relations', card: 'relations', cols: 12},
    article: {tab: 'relations', card: 'relations', cols: 12},
    look: {tab: 'relations', card: 'relations', cols: 12},
    product: {tab: 'relations', card: 'relations', cols: 12},
    productStream: {tab: 'relations', card: 'relations', cols: 12},
    order: {tab: 'relations', card: 'relations', cols: 12},
    merchant: {tab: 'relations', card: 'relations', cols: 12},
    category: {tab: 'relations', card: 'relations', cols: 12},
    site: {tab: 'relations', card: 'relations', cols: 12},
    client: {tab: 'relations', card: 'relations', cols: 12},
    language: {tab: 'relations', card: 'relations', cols: 12},
    creator: {tab: 'general', card: 'general', cols: 12},

    // Custom Fields
    customFields: {tab: 'customFields', card: 'customFields', componentName: 'sw-custom-field-set-renderer', cols: 12},
    custom1: {tab: 'customFields', card: 'customFields', cols: 6},
    custom2: {tab: 'customFields', card: 'customFields', cols: 6},
    custom3: {tab: 'customFields', card: 'customFields', cols: 6},
    custom4: {tab: 'customFields', card: 'customFields', cols: 6},

    // Media
    bannerColor: {tab: 'general', card: 'media', componentName: 'sw-colorpicker', cols: 12},

    // Comments
    enableComments: {tab: 'comments', card: 'general', cols: 12}
};

export default mapping;
