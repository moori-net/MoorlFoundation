const mapping = {
  // Contact
  salutation: { tab: 'general', card: 'contact', attributes: { labelProperty: 'displayName' } },
  title: { tab: 'general', card: 'contact' },
  firstName: { tab: 'general', card: 'contact' },
  lastName: { tab: 'general', card: 'contact' },
  email: { tab: 'general', card: 'contact', componentName: 'sw-email-field' },
  phoneNumber: { tab: 'general', card: 'contact' },
  shopUrl: { tab: 'general', card: 'contact', componentName: 'sw-url-field' },
  merchantUrl: { tab: 'general', card: 'contact', componentName: 'sw-url-field' },

  // Profile
  creatorType: { tab: 'general', card: 'profile' },
  followers: { tab: 'general', card: 'profile', attributes: { labelProperty: 'customerNumber' } },
  role: { tab: 'general', card: 'profile' },
  link: { tab: 'general', card: 'profile', componentName: 'sw-url-field' },
  imprint: { tab: 'general', card: 'profile' },
  customer: { tab: 'general', card: 'profile', attributes: { labelProperty: 'customerNumber' } },

  // Things
  autoIncrement: { tab: 'general', card: 'general' },
  date: { tab: 'general', card: 'general' },
  name: { tab: 'general', card: 'general' },
  teaser: { tab: 'general', card: 'general' },
  description: { tab: 'general', card: 'general' },
  content: { tab: 'general', card: 'general' },
  contentCmsPage: { tab: 'general', card: 'general' },
  descriptionHtml: { tab: 'general', card: 'general' },
  keywords: { tab: 'general', card: 'general' },
  type: { tab: 'general', card: 'general' },
  highlight: { tab: 'general', card: 'general' },
  creator: { tab: 'general', card: 'general' },

  // SEO / Meta
  schemaOrgType: { tab: 'seo', card: 'general' },
  schemaOrgProperty: { tab: 'seo', card: 'general' },
  metaTitle: { tab: 'seo', card: 'general' },
  metaDescription: { tab: 'seo', card: 'general' },
  metaKeywords: { tab: 'seo', card: 'general' },
  seoUrls: { tab: 'seo', componentName: 'sw-seo-url' },

  // CMS Page
  cmsPage: { tab: 'cmsPage', card: 'cmsPage', componentName: 'moorl-layout-card-v2' },

  // Visibility
  active: { tab: 'general', card: 'visibility' },
  priority: { tab: 'general', card: 'visibility' },
  sticky: { tab: 'general', card: 'visibility' },
  invisible: { tab: 'general', card: 'visibility' },
  showFrom: { tab: 'general', card: 'visibility' },
  showUntil: { tab: 'general', card: 'visibility' },
  category: { tab: 'general', card: 'visibility' },
  categories: { tab: 'general', card: 'visibility' },
  swCategories: { tab: 'general', card: 'visibility' },
  salesChannel: { tab: 'general', card: 'visibility' },
  salesChannels: { tab: 'general', card: 'visibility' },
  customers: { tab: 'general', card: 'visibility', attributes: { labelProperty: 'customerNumber' } },
  customerGroup: { tab: 'general', card: 'visibility' },
  customerGroups: { tab: 'general', card: 'visibility' },
  article: { tab: 'general', card: 'visibility' },

  // Address
  street: { tab: 'address', card: 'general' },
  streetNumber: { tab: 'address', card: 'general' },
  zipcode: { tab: 'address', card: 'general' },
  city: { tab: 'address', card: 'general' },
  countryCode: { tab: 'address', card: 'general' },
  country: { tab: 'address', card: 'general' },
  countryState: { tab: 'address', card: 'general' },
  additionalAddressLine1: { tab: 'address', card: 'general' },
  additionalAddressLine2: { tab: 'address', card: 'general' },
  locationPlaceId: { tab: 'address', card: 'general' },

  // Company
  company: { tab: 'general', card: 'company' },
  department: { tab: 'general', card: 'company' },
  executiveDirector: { tab: 'general', card: 'company' },
  placeOfFulfillment: { tab: 'general', card: 'company' },
  placeOfJurisdiction: { tab: 'general', card: 'company' },
  bankBic: { tab: 'general', card: 'company' },
  bankIban: { tab: 'general', card: 'company' },
  bankName: { tab: 'general', card: 'company' },
  taxOffice: { tab: 'general', card: 'company' },
  taxNumber: { tab: 'general', card: 'company' },
  vatId: { tab: 'general', card: 'company' },

  // Location
  locationLat: { tab: 'address', card: 'location' },
  locationLon: { tab: 'address', card: 'location' },
  autoLocation: { tab: 'address', card: 'location' },
  marker: { tab: 'address', card: 'location' },

  // Time
  timeZone: { tab: 'time', card: 'general', componentName: 'moorl-select-field', attributes: { set: 'timeZone' } },
  openingHours: { tab: 'time', card: 'general', componentName: 'moorl-opening-hours' },
  showOpeningHours: { tab: 'time', card: 'general' },

  // Relations
  products: { tab: 'relations', card: 'general', attributes: { labelProperty: 'productNumber' } },
  productManufacturers: { tab: 'relations', card: 'general' },
  tags: { tab: 'relations', card: 'general' },
  medias: { tab: 'relations', card: 'general' },
  downloads: { tab: 'relations', card: 'general' },
  look: { tab: 'relations', card: 'general' },
  product: { tab: 'relations', card: 'general' },

  // Custom Fields
  customFields: { tab: 'customFields', card: 'customFields', componentName: 'sw-custom-field-set-renderer' },
  custom1: { tab: 'customFields', card: 'customFields' },
  custom2: { tab: 'customFields', card: 'customFields' },
  custom3: { tab: 'customFields', card: 'customFields' },
  custom4: { tab: 'customFields', card: 'customFields' },

  // Media
  bannerColor: { tab: 'general', card: 'media', componentName: 'sw-colorpicker' },

  // Comments
  enableComments: { tab: 'comments', card: 'general' }
};

export default mapping;
