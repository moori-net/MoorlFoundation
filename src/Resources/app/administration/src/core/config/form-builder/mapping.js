const mapping = {
    // Other
    children: {tab: 'general', card: 'general'},
    directory: {
        tab: 'directory', card: 'directory', componentName: 'moorl-file-explorer',
        conditions: [{property: 'clientId', value: null, operator: '!=='}],
        attributes: {clientId: ({item}) => item.clientId, showActions: false},
        cols: 12
    },
    deeplink: {tab: 'customFields', card: 'deeplink'},

    // Promotion
    discountType: {
        tab: 'promotion',
        card: 'promotion',
        componentName: 'moorl-select-field',
        attributes: {
            customSet: ['percentage'],
            snippetPath: 'moorl-foundation.field'
        }
    },
    discountValue: {tab: 'promotion', card: 'promotion'},
    maxStacks: {tab: 'promotion', card: 'promotion'},

    // Contact
    salutation: {tab: 'general', card: 'contact', cols: 3},
    title: {tab: 'general', card: 'contact', cols: 3},
    firstName: {tab: 'general', card: 'contact', cols: 3},
    lastName: {tab: 'general', card: 'contact', cols: 3},
    email: {tab: 'general', card: 'contact', componentName: 'mt-email-field'},
    phoneNumber: {tab: 'general', card: 'contact'},
    shopUrl: {tab: 'general', card: 'contact', componentName: 'mt-url-field'},
    merchantUrl: {tab: 'general', card: 'contact', componentName: 'mt-url-field'},
    externalLink: {tab: 'general', card: 'contact', componentName: 'mt-url-field'},

    // Profile
    creatorType: {tab: 'general', card: 'profile'},
    followers: {tab: 'general', card: 'profile'},
    role: {tab: 'general', card: 'profile'},
    link: {tab: 'general', card: 'profile', componentName: 'mt-url-field'},
    isBusiness: {tab: 'general', card: 'profile'},
    imprint: {tab: 'general', card: 'profile'},

    // Options
    isStock: {tab: 'general', card: 'options'},
    stockType: {
        tab: 'general', card: 'options',
        conditions: [{property: 'isStock', value: true, operator: 'eq'}],
        componentName: 'moorl-select-field',
        attributes: {set: 'stockType'}
    },
    isCountdown: {tab: 'general', card: 'options'},
    countdownType: {
        tab: 'general', card: 'options',
        conditions: [{property: 'isCountdown', value: true, operator: 'eq'}],
        componentName: 'moorl-select-field',
        attributes: {set: 'countdownType'}
    },
    isGamble: {tab: 'general', card: 'options'},
    gambleType: {
        tab: 'general', card: 'options',
        conditions: [{property: 'isGamble', value: true, operator: 'eq'}],
        componentName: 'moorl-select-field',
        attributes: {set: 'interval'}
    },
    hasFiles: {tab: 'general', card: 'options'},
    hasCodes: {tab: 'general', card: 'options'},
    hasLinks: {tab: 'general', card: 'options'},
    configParent: {tab: 'general', card: 'options'},
    config: {tab: 'general', card: 'options'},

    // Price
    tax: {tab: 'price', card: 'price'},
    customPrice: {tab: 'price', card: 'price'},

    // Stock
    stock: {tab: 'stock', card: 'general'},
    sales: {tab: 'stock', card: 'general'},
    availableStock: {tab: 'stock', card: 'general'},
    deliveryTime: {tab: 'stock', card: 'general'},

    // Things
    question: {tab: 'general', card: 'general'},
    solution: {tab: 'general', card: 'general'},
    isTrue: {tab: 'general', card: 'general'},
    name: {tab: 'general', card: 'general'},
    alias: {tab: 'general', card: 'general'},
    expiryTime: {tab: 'general', card: 'general'},
    technicalName: {tab: 'general', card: 'general'},
    internalName: {tab: 'general', card: 'general'},
    highlight: {tab: 'general', card: 'general'},
    fixed: {tab: 'general', card: 'general'},
    logical: {tab: 'general', card: 'general'},
    type: {tab: 'general', card: 'general'},
    calculator: {tab: 'general', card: 'general'},
    levelCount: {tab: 'general', card: 'general'},
    autoIncrement: {tab: 'general', card: 'general'},
    date: {tab: 'general', card: 'general'},
    releaseDate: {tab: 'general', card: 'general'},
    optionPercentage: {tab: 'general', card: 'general'},
    redeemCode: {tab: 'general', card: 'general'},
    showDiscount: {tab: 'general', card: 'general'},
    subscriptionTime: {tab: 'general', card: 'general'},
    teaser: {tab: 'general', card: 'general'},
    description: {tab: 'general', card: 'general'},
    content: {tab: 'general', card: 'general'},
    contentCmsPage: {tab: 'general', card: 'general'},
    descriptionHtml: {tab: 'general', card: 'general'},
    keywords: {tab: 'general', card: 'general'},
    info: {tab: 'general', card: 'general'},
    customerNumber: {tab: 'general', card: 'general'},
    manufacturerNumber: {tab: 'general', card: 'general'},
    ean: {tab: 'general', card: 'general'},
    useChapters: {tab: 'general', card: 'general'},
    validUntil: {tab: 'general', card: 'general'},
    code: {tab: 'general', card: 'general'},

    // Specifications
    unit: {tab: 'general', card: 'specifications'},
    referenceUnit: {tab: 'general', card: 'specifications'},

    // Visibility
    active: {tab: 'general', card: 'visibility'},
    priority: {tab: 'general', card: 'visibility'},
    badge: {tab: 'general', card: 'visibility'},
    gallery: {tab: 'general', card: 'visibility'},
    isDefault: {tab: 'general', card: 'visibility'},
    tab: {tab: 'general', card: 'visibility'},
    approved: {tab: 'general', card: 'visibility'},
    available: {tab: 'general', card: 'visibility'},
    sticky: {tab: 'general', card: 'visibility'},
    invisible: {tab: 'general', card: 'visibility'},
    released: {tab: 'general', card: 'visibility'},
    published: {tab: 'general', card: 'visibility'},
    position: {tab: 'general', card: 'visibility'},
    hidden: {tab: 'general', card: 'visibility'},
    isPreview: {tab: 'general', card: 'visibility'},
    showFrom: {tab: 'general', card: 'visibility', newline: true},
    showUntil: {tab: 'general', card: 'visibility'},
    boostTopUntil: {tab: 'general', card: 'visibility', newline: true},
    boostHomeUntil: {tab: 'general', card: 'visibility'},
    boostSlotUntil: {tab: 'general', card: 'visibility'},
    categories: {tab: 'general', card: 'visibility'},
    languages: {tab: 'general', card: 'visibility'},
    languageIds: {
        tab: 'general',
        card: 'visibility',
        componentName: 'sw-entity-multi-id-select',
        attributes: {entity: 'language'}
    },
    swCategories: {tab: 'general', card: 'visibility'},
    shopwareCategories: {tab: 'general', card: 'visibility'},
    salesChannels: {tab: 'general', card: 'visibility'},
    customers: {tab: 'general', card: 'visibility'},
    sorting: {tab: 'general', card: 'visibility'},
    customerGroups: {tab: 'general', card: 'visibility'},
    redirectFix: {tab: 'general', card: 'visibility'},
    language: {tab: 'general', card: 'visibility'},
    cssClass: {tab: 'general', card: 'visibility'},
    minResults: {tab: 'general', card: 'visibility'},
    page: {tab: 'general', card: 'visibility'},
    availabilityRule: {tab: 'general', card: 'visibility'},
    visible: {tab: 'general', card: 'visibility'},

    // SEO / Meta
    schemaOrgType: {tab: 'seo', card: 'general'},
    schemaOrgProperty: {tab: 'seo', card: 'general'},
    metaTitle: {tab: 'seo', card: 'general'},
    metaDescription: {tab: 'seo', card: 'general'},
    metaKeywords: {tab: 'seo', card: 'general'},
    seoUrls: {tab: 'seo', componentName: 'sw-seo-url'},
    progress: {tab: 'seo', card: 'meta'},
    duration: {tab: 'seo', card: 'meta'},
    fileCount: {tab: 'seo', card: 'meta'},
    lessonCount: {tab: 'seo', card: 'meta'},
    chapterCount: {tab: 'seo', card: 'meta'},
    boardCount: {tab: 'seo', card: 'meta'},
    points: {tab: 'seo', card: 'meta'},
    ratings: {tab: 'seo', card: 'meta'},
    subscriptionCount: {tab: 'seo', card: 'meta'},
    testCount: {tab: 'seo', card: 'meta'},
    ratingCount: {tab: 'seo', card: 'meta'},
    courseCount: {tab: 'seo', card: 'meta'},

    // Event
    eventDate: {tab: 'event', card: 'event'},
    eventDuration: {tab: 'event', card: 'event'},
    eventLocation: {tab: 'event', card: 'event'},

    // Address
    street: {tab: 'address', card: 'general', cols: 8},
    streetNumber: {tab: 'address', card: 'general', cols: 4},
    zipcode: {tab: 'address', card: 'general', cols: 4},
    city: {tab: 'address', card: 'general', cols: 8},
    countryCode: {tab: 'address', card: 'general', cols: 2},
    country: {tab: 'address', card: 'general', cols: 5},
    countryState: {tab: 'address', card: 'general', cols: 5},
    additionalAddressLine1: {tab: 'address', card: 'general'},
    additionalAddressLine2: {tab: 'address', card: 'general'},
    locationPlaceId: {tab: 'address', card: 'general'},

    // Company
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

    // Location
    locationLat: {tab: 'address', card: 'location'},
    locationLon: {tab: 'address', card: 'location'},
    autoLocation: {tab: 'address', card: 'location'},
    marker: {tab: 'address', card: 'location'},

    // Use this prop as placeholder
    locationCache: {
        tab: 'address',
        componentName: 'moorl-location-card',
        attributes: {
            item: ({item}) => item
        }
    },

    // Time
    timeZone: {tab: 'time', card: 'general', componentName: 'moorl-select-field', attributes: {set: 'timeZone'}},
    showOpeningHours: {tab: 'time', card: 'general'},
    openingHours: {
        tab: 'time',
        card: 'general',
        componentName: 'moorl-opening-hours',
        conditions: [
            {property: 'showOpeningHours', value: true, operator: 'eq'}
        ]
    },

    // Relations
    products: {tab: 'relations', card: 'relations'},
    accessories: {tab: 'relations', card: 'relations'},
    productManufacturers: {tab: 'relations', card: 'relations'},
    productStreams: {tab: 'relations', card: 'relations'},
    filters: {tab: 'relations', card: 'relations'},
    tags: {tab: 'relations', card: 'relations'},
    medias: {tab: 'relations', card: 'relations'},
    downloads: {tab: 'relations', card: 'relations'},
    lessons: {tab: 'relations', card: 'relations'},
    courses: {tab: 'relations', card: 'relations'},
    chapters: {tab: 'relations', card: 'relations'},
    files: {tab: 'relations', card: 'relations'},
    salesChannel: {tab: 'relations', card: 'relations'},
    customer: {tab: 'relations', card: 'relations'},
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
    filterGroup: {tab: 'relations', card: 'relations'},
    parent: {tab: 'relations', card: 'relations'},
    lesson: {tab: 'relations', card: 'relations'},
    course: {tab: 'relations', card: 'relations'},
    chapter: {tab: 'relations', card: 'relations'},
    board: {tab: 'relations', card: 'relations', attributes: {labelProperty: 'content'}},
    downloadCenter: {tab: 'relations', card: 'relations'},
    downloadCenterCode: {tab: 'relations', card: 'relations'},
    partsListConfigurator: {tab: 'relations', card: 'relations'},

    creator: {tab: 'general', card: 'general'},
    tutor: {tab: 'general', card: 'general'},
    categoriesRo: {hidden: true},
    categoryIds: {hidden: true},
    categoryTree: {hidden: true},

    // Media
    cover: {tab: 'general', card: 'media'},
    color: {tab: 'general', card: 'media'},
    bannerColor: {tab: 'general', card: 'media'},
    mediaFolder: {tab: 'general', card: 'media'},
    iconColor: {tab: 'general', card: 'media'},
    backgroundColor: {tab: 'general', card: 'media'},
    minWidth: {tab: 'general', card: 'media'},
    embeddedMedia: {tab: 'general', card: 'media'},
    icon: {tab: 'general', card: 'media'},
    sgMedia: {tab: 'general', card: 'media', componentName: 'moorl-entity-select-field'},
    embeddedId: {tab: 'general', card: 'media'},
    embeddedUrl: {
        tab: 'general',
        card: 'media',
        componentName: 'moorl-embedded-url-field',
        cols: 12,
        attributes: {
            backgroundColor: ({item}) => item.backgroundColor
        }
    },
    videos: {tab: 'general'},

    // Comments
    enableComments: {tab: 'comments', card: 'general'}
};

export default mapping;
