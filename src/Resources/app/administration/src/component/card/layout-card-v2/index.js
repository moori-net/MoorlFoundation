import template from './index.html.twig';
import './index.scss';

const { Criteria, ChangesetGenerator } = Shopware.Data;
const type = Shopware.Utils.types;
const { cloneDeep, merge } = Shopware.Utils.object;

Shopware.Component.register('moorl-layout-card-v2', {
    template,

    inject: ['repositoryFactory', 'cmsService'],

    props: {
        item: {
            type: Object,
            required: true,
        },
        entity: {
            type: String,
            required: true,
        },
        pageType: {
            type: String,
            required: true,
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
        pageTypes: {
            type: Array,
            required: false,
            default() {
                return [];
            },
        },
        headline: {
            type: String,
            required: false,
            default: '',
        },
    },

    data() {
        return {
            showLayoutSelectionModal: false,
        };
    },

    computed: {
        pageTypeCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addFilter(Criteria.equals('type', this.pageType));

            return criteria;
        },
        cmsPageRepository() {
            return this.repositoryFactory.create('cms_page');
        },
        cmsPageId() {
            return this.item ? this.item.cmsPageId : null;
        },
        cmsPage() {
            return Shopware.Store.get('cmsPage').currentPage;
        },
        cmsPageTypes() {
            return {
                page: this.$tc('sw-cms.detail.label.pageTypeShopPage'),
                landingpage: this.$tc(
                    'sw-cms.detail.label.pageTypeLandingpage'
                ),
                product_list: this.$tc('sw-cms.detail.label.pageTypeCategory'),
                product_detail: this.$tc('sw-cms.detail.label.pageTypeProduct'),
                creator_detail: this.$tc('moorl-creator.general.creator'),
                magazine_article_detail: this.$tc(
                    'moorl-magazine.general.article'
                ),
                merchant_detail: this.$tc(
                    'moorl-merchant-finder.general.mainMenuItemGeneral'
                ),
                lexicon_detail: this.$tc(
                    'sw-seo-url-template-card.routeNames.moorl-lexicon-lexicon-page'
                ),
            };
        },
    },

    watch: {
        cmsPageId() {
            Shopware.Store.get('cmsPage').resetCmsPageState();
            this.getAssignedCmsPage();
        },
    },

    created() {
        Shopware.Store.get('cmsPage').resetCmsPageState();

        if (this.pageTypes.length === 0) {
            this.pageTypes.push(this.pageType);
        }

        this.getAssignedCmsPage();
    },

    methods: {
        saveCmsConfig() {
            const pageOverrides = this.getCmsPageOverrides();

            if (type.isPlainObject(pageOverrides)) {
                this.item.slotConfig = cloneDeep(pageOverrides);
            }

            this.$emit('save-cms-config');
        },
        onLayoutSelect(selectedLayout) {
            this.item.cmsPageId = selectedLayout;
        },
        onLayoutReset() {
            this.onLayoutSelect(null);
        },
        openInPagebuilder() {
            if (!this.cmsPage) {
                this.$router.push({ name: 'sw.cms.create' });
            } else {
                this.$router.push({
                    name: 'sw.cms.detail',
                    params: { id: this.item.cmsPageId },
                });
            }
        },
        openLayoutModal() {
            this.showLayoutSelectionModal = true;
        },
        closeLayoutModal() {
            this.showLayoutSelectionModal = false;
        },
        getAssignedCmsPage() {
            if (this.cmsPageId === null) {
                return Promise.resolve(null);
            }
            const cmsPageId = this.cmsPageId;
            const criteria = new Criteria(1, 1);
            criteria.setIds([cmsPageId]);
            criteria.addAssociation('previewMedia');
            criteria.addAssociation('sections');
            criteria
                .getAssociation('sections')
                .addSorting(Criteria.sort('position'));
            criteria.addAssociation('sections.blocks');
            criteria
                .getAssociation('sections.blocks')
                .addSorting(Criteria.sort('position', 'ASC'))
                .addAssociation('slots');

            return this.cmsPageRepository.search(criteria).then((response) => {
                const cmsPage = response.get(cmsPageId);
                if (cmsPageId !== this.cmsPageId) {
                    return null;
                }
                if (this.item.slotConfig !== null) {
                    cmsPage.sections.forEach((section) => {
                        section.blocks.forEach((block) => {
                            block.slots.forEach((slot) => {
                                if (this.item.slotConfig[slot.id]) {
                                    if (slot.config === null) {
                                        slot.config = {};
                                    }
                                    merge(
                                        slot.config,
                                        cloneDeep(this.item.slotConfig[slot.id])
                                    );
                                }
                            });
                        });
                    });
                }
                this.updateCmsPageDataMapping();
                Shopware.Store.get('cmsPage').setCurrentPage(cmsPage);

                return this.cmsPage;
            });
        },
        updateCmsPageDataMapping() {
            Shopware.Store.get('cmsPage').setCurrentMappingEntity(this.entity);
            Shopware.Store.get('cmsPage').setCurrentMappingTypes(
                this.cmsService.getEntityMappingTypes(this.pageType)
            );
            Shopware.Store.get('cmsPage').setCurrentDemoEntity(this.item);
        },
        getCmsPageOverrides() {
            if (this.cmsPage === null) {
                return null;
            }
            this.deleteSpecifcKeys(this.cmsPage.sections);
            const changesetGenerator = new ChangesetGenerator();
            const { changes } = changesetGenerator.generate(this.cmsPage);
            const slotOverrides = {};
            if (changes === null) {
                return slotOverrides;
            }
            if (type.isArray(changes.sections)) {
                changes.sections.forEach((section) => {
                    if (type.isArray(section.blocks)) {
                        section.blocks.forEach((block) => {
                            if (type.isArray(block.slots)) {
                                block.slots.forEach((slot) => {
                                    slotOverrides[slot.id] = slot.config;
                                });
                            }
                        });
                    }
                });
            }
            return slotOverrides;
        },
        deleteSpecifcKeys(sections) {
            if (!sections) {
                return;
            }
            sections.forEach((section) => {
                if (!section.blocks) {
                    return;
                }
                section.blocks.forEach((block) => {
                    if (!block.slots) {
                        return;
                    }
                    block.slots.forEach((slot) => {
                        if (!slot.config) {
                            return;
                        }
                        Object.values(slot.config).forEach((configField) => {
                            if (configField.entity) {
                                delete configField.entity;
                            }
                            if (configField.hasOwnProperty('required')) {
                                delete configField.required;
                            }
                            if (configField.type) {
                                delete configField.type;
                            }
                        });
                    });
                });
            });
        },
    },
});
