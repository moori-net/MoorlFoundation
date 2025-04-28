const mapping = {
    // Other
    children: {tab: 'general', card: 'general'},
    directory: {
        tab: 'directory', card: 'directory', componentName: 'moorl-file-explorer',
        conditions: [{property: 'clientId', value: null, operator: '!=='}],
        attributes: {clientId: ({item}) => item.clientId, showActions: false}
    },
    deeplink: {tab: 'customFields', card: 'deeplink'},

    // Promotion
    discountValue: {tab: 'promotion', card: 'promotion'},

    // Contact
    salutation: {tab: 'general', card: 'contact', attributes: {labelProperty: 'displayName'}, cols: 3},
    title: {tab: 'general', card: 'contact', cols: 3},
    firstName: {tab: 'general', card: 'contact', cols: 3},
    lastName: {tab: 'general', card: 'contact', cols: 3},
    email: {tab: 'general', card: 'contact', componentName: 'sw-email-field', cols: 6},
    phoneNumber: {tab: 'general', card: 'contact', cols: 6},
    shopUrl: {tab: 'general', card: 'contact', componentName: 'sw-url-field', cols: 6},
    merchantUrl: {tab: 'general', card: 'contact', componentName: 'sw-url-field', cols: 6},
    externalLink: {tab: 'general', card: 'contact', componentName: 'sw-url-field', cols: 6},

    // Profile
    creatorType: {tab: 'general', card: 'profile'},
    followers: {tab: 'general', card: 'profile', attributes: {labelProperty: 'customerNumber'}},
    role: {tab: 'general', card: 'profile'},
    link: {tab: 'general', card: 'profile', componentName: 'sw-url-field'},
    isBusiness: {tab: 'general', card: 'profile'},
    imprint: {tab: 'general', card: 'profile'},

    // Options
    isStock: {tab: 'general', card: 'options', cols: 6},
    stockType: {
        tab: 'general', card: 'options', cols: 6,
        conditions: [{property: 'isStock', value: true, operator: 'eq'}],
        componentName: 'moorl-select-field',
        attributes: {set: 'stockType'}
    },
    isCountdown: {tab: 'general', card: 'options', cols: 6},
    countdownType: {
        tab: 'general', card: 'options', cols: 6,
        conditions: [{property: 'isCountdown', value: true, operator: 'eq'}],
        componentName: 'moorl-select-field',
        attributes: {set: 'countdownType'}
    },
    isGamble: {tab: 'general', card: 'options', cols: 6},
    gambleType: {
        tab: 'general', card: 'options', cols: 6,
        conditions: [{property: 'isGamble', value: true, operator: 'eq'}],
        componentName: 'moorl-select-field',
        attributes: {set: 'interval'}
    },
    hasFiles: {tab: 'general', card: 'options', cols: 6},
    hasCodes: {tab: 'general', card: 'options', cols: 6},
    hasLinks: {tab: 'general', card: 'options', cols: 6},

    // Price
    tax: {tab: 'price', card: 'price'},
    customPrice: {tab: 'price', card: 'price'},

    // Stock
    stock: {tab: 'stock', card: 'general'},
    sales: {tab: 'stock', card: 'general'},
    availableStock: {tab: 'stock', card: 'general'},
    deliveryTime: {tab: 'stock', card: 'general'},

    // General

    // Things
    question: {tab: 'general', card: 'general'},
    solution: {tab: 'general', card: 'general'},
    isTrue: {tab: 'general', card: 'general'},
    name: {tab: 'general', card: 'general'},
    alias: {tab: 'general', card: 'general', cols: 6},
    expiryTime: {tab: 'general', card: 'general', cols: 6},
    technicalName: {tab: 'general', card: 'general', cols: 6},
    internalName: {tab: 'general', card: 'general', cols: 6},
    highlight: {tab: 'general', card: 'general', cols: 6},
    type: {tab: 'general', card: 'general', cols: 6},
    levelCount: {tab: 'general', card: 'general', cols: 6},
    autoIncrement: {tab: 'general', card: 'general', cols: 6},
    date: {tab: 'general', card: 'general', cols: 6},
    releaseDate: {tab: 'general', card: 'general', cols: 6},
    optionPercentage: {tab: 'general', card: 'general', cols: 6},
    redeemCode: {tab: 'general', card: 'general', cols: 6},
    showDiscount: {tab: 'general', card: 'general', cols: 6},
    subscriptionTime: {tab: 'general', card: 'general', cols: 6},
    teaser: {tab: 'general', card: 'general'},
    description: {tab: 'general', card: 'general'},
    content: {tab: 'general', card: 'general'},
    contentCmsPage: {tab: 'general', card: 'general'},
    descriptionHtml: {tab: 'general', card: 'general'},
    keywords: {tab: 'general', card: 'general'},
    info: {tab: 'general', card: 'general'},
    customerNumber: {tab: 'general', card: 'general', cols: 6},
    manufacturerNumber: {tab: 'general', card: 'general', cols: 6},
    ean: {tab: 'general', card: 'general', cols: 6},
    useChapters: {tab: 'general', card: 'general', cols: 6},

    // SEO / Meta
    schemaOrgType: {tab: 'seo', card: 'general'},
    schemaOrgProperty: {tab: 'seo', card: 'general'},
    metaTitle: {tab: 'seo', card: 'general'},
    metaDescription: {tab: 'seo', card: 'general'},
    metaKeywords: {tab: 'seo', card: 'general'},
    seoUrls: {tab: 'seo', componentName: 'sw-seo-url'},

    duration: {tab: 'seo', card: 'meta', cols: 6},
    fileCount: {tab: 'seo', card: 'meta', cols: 6},
    lessonCount: {tab: 'seo', card: 'meta', cols: 6},
    chapterCount: {tab: 'seo', card: 'meta', cols: 6},
    boardCount: {tab: 'seo', card: 'meta', cols: 6},
    points: {tab: 'seo', card: 'meta', cols: 6},
    ratings: {tab: 'seo', card: 'meta', cols: 6},
    subscriptionCount: {tab: 'seo', card: 'meta', cols: 6},
    testCount: {tab: 'seo', card: 'meta', cols: 6},
    ratingCount: {tab: 'seo', card: 'meta', cols: 6},
    courseCount: {tab: 'seo', card: 'meta', cols: 6},

    // CMS Page
    cmsPage: {tab: 'cmsPage', card: 'cmsPage'},

    // Event
    eventDate: {tab: 'event', card: 'event', cols: 6},
    eventDuration: {tab: 'event', card: 'event', cols: 6},
    eventLocation: {tab: 'event', card: 'event', cols: 6},

    // Visibility
    active: {tab: 'general', card: 'visibility', cols: 6},
    approved: {tab: 'general', card: 'visibility', cols: 6},
    available: {tab: 'general', card: 'visibility', cols: 6},
    sticky: {tab: 'general', card: 'visibility', cols: 6},
    invisible: {tab: 'general', card: 'visibility', cols: 6},
    released: {tab: 'general', card: 'visibility', cols: 6},
    published: {tab: 'general', card: 'visibility', cols: 6},
    priority: {tab: 'general', card: 'visibility', cols: 6},
    position: {tab: 'general', card: 'visibility', cols: 6},
    showFrom: {tab: 'general', card: 'visibility', cols: 6, newline: true},
    showUntil: {tab: 'general', card: 'visibility', cols: 6},
    boostTopUntil: {tab: 'general', card: 'visibility', cols: 6, newline: true},
    boostHomeUntil: {tab: 'general', card: 'visibility', cols: 6},
    boostSlotUntil: {tab: 'general', card: 'visibility', cols: 6},
    categories: {tab: 'general', card: 'visibility'},
    swCategories: {tab: 'general', card: 'visibility'},
    shopwareCategories: {tab: 'general', card: 'visibility'},
    salesChannels: {tab: 'general', card: 'visibility'},
    customers: {tab: 'general', card: 'visibility', attributes: {labelProperty: 'customerNumber'}},
    customerGroups: {tab: 'general', card: 'visibility'},
    redirectFix: {tab: 'general', card: 'visibility'},
    language: {tab: 'general', card: 'visibility'},
    cssClass: {tab: 'general', card: 'visibility', cols: 6},
    minResults: {tab: 'general', card: 'visibility', cols: 6},
    page: {tab: 'general', card: 'visibility', cols: 6},

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
    locationPlaceId: {tab: 'address', card: 'general'},

    // Company
    company: {tab: 'general', card: 'company'},
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
    locationCache: {
        hidden: true,
        tab: 'address',
        card: 'location',
        componentName: 'moorl-entity-grid-card-v2',
        attributes: {
            defaultItem: ({item}) => ({entityId: item.id})
        },
    },

    // Time
    timeZone: {tab: 'time', card: 'general', componentName: 'moorl-select-field', attributes: {set: 'timeZone'}, cols: 6},
    showOpeningHours: {tab: 'time', card: 'general', cols: 6},
    openingHours: {
        tab: 'time',
        card: 'general',
        componentName: 'moorl-opening-hours',
        conditions: [
            {property: 'showOpeningHours', value: true, operator: 'eq'}
        ]
    },

    // Relations
    products: {tab: 'relations', card: 'relations', attributes: {labelProperty: 'productNumber'}},
    accessories: {tab: 'relations', card: 'relations', attributes: {labelProperty: 'productNumber'}},
    productManufacturers: {tab: 'relations', card: 'relations'},
    tags: {tab: 'relations', card: 'relations'},
    medias: {tab: 'relations', card: 'relations'},
    downloads: {tab: 'relations', card: 'relations'},
    lessons: {tab: 'relations', card: 'relations'},
    courses: {tab: 'relations', card: 'relations'},
    chapters: {tab: 'relations', card: 'relations'},
    files: {tab: 'relations', card: 'relations'},

    salesChannel: {tab: 'relations', card: 'relations'},
    customer: {tab: 'relations', card: 'relations', attributes: {labelProperty: 'customerNumber'}},
    customerGroup: {tab: 'relations', card: 'relations'},
    article: {tab: 'relations', card: 'relations'},
    look: {tab: 'relations', card: 'relations'},
    appflixAd: {tab: 'relations', card: 'relations'},
    product: {tab: 'relations', card: 'relations'},
    accessory: {tab: 'relations', card: 'relations'},
    productStream: {tab: 'relations', card: 'relations'},
    order: {tab: 'relations', card: 'relations'},
    merchant: {tab: 'relations', card: 'relations'},
    category: {tab: 'relations', card: 'relations'},
    site: {tab: 'relations', card: 'relations'},
    client: {tab: 'relations', card: 'relations'},
    lesson: {tab: 'relations', card: 'relations'},
    course: {tab: 'relations', card: 'relations'},
    chapter: {tab: 'relations', card: 'relations'},
    board: {tab: 'relations', card: 'relations', attributes: {labelProperty: 'content'}},

    creator: {tab: 'general', card: 'general'},
    tutor: {tab: 'general', card: 'general'},

    categoriesRo: {hidden: true},
    categoryIds: {hidden: true},
    categoryTree: {hidden: true},

    // Custom Fields
    customFields: {tab: 'customFields', card: 'customFields', componentName: 'sw-custom-field-set-renderer'},
    custom1: {tab: 'customFields', card: 'customFields', cols: 6},
    custom2: {tab: 'customFields', card: 'customFields', cols: 6},
    custom3: {tab: 'customFields', card: 'customFields', cols: 6},
    custom4: {tab: 'customFields', card: 'customFields', cols: 6},

    // Media
    cover: {hidden: true},
    color: {tab: 'general', card: 'media', componentName: 'sw-colorpicker', cols: 6},
    bannerColor: {tab: 'general', card: 'media', componentName: 'sw-colorpicker', cols: 6},
    mediaFolder: {tab: 'general', card: 'media', cols: 6},
    icon: {tab: 'general', card: 'media', cols: 6},
    sgMedia: {tab: 'general', card: 'media', cols: 6},

    // Comments
    enableComments: {tab: 'comments', card: 'general'}
};

export default mapping;
