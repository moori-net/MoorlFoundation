import template from './index.html.twig';

const {Component} = Shopware;
const {mapPropertyErrors} = Shopware.Component.getComponentHelper();

Component.register('moorl-thing-card', {
    template,

    inject: [
        'repositoryFactory'
    ],

    props: {
        item: {
            type: Object,
            required: true,
        },
        entity: {
            type: String,
            required: false,
            default: 'moorl_merchant'
        },
        pageType: {
            type: String,
            required: false,
            default: 'merchant_detail'
        },
    },

    data() {
        return {
            mediaModalIsOpen: false
        };
    },

    computed: {
        ...mapPropertyErrors('item', [
            'name'
        ]),

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        pageTypeCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addFilter(
                Criteria.equals('type', this.pageType),
            );

            return criteria;
        },

        merchantCustomerFilterColumns() {
            return [
                'customer.customerNumber',
                'customer.email',
                'customer.lastName',
                'customerNumber',
                'info'
            ];
        },

        cmsPageRepository() {
            return this.repositoryFactory.create('cms_page');
        },

        cmsPageId() {
            return this.item ? this.item.cmsPageId : null;
        },

        cmsPage() {
            return Shopware.State.get('cmsPageState').currentPage;
        },
    },

    watch: {
        cmsPageId() {
            if (this.isLoading) {
                return;
            }

            if (this.item) {
                this.item.slotConfig = null;
                Shopware.State.dispatch('cmsPageState/resetCmsPageState').then(this.getAssignedCmsPage);
            }
        }
    },

    created() {
        Shopware.State.dispatch('cmsPageState/resetCmsPageState');
    },

    methods: {
        setMediaItem({targetId}) {
            this.mediaRepository.get(targetId, Shopware.Context.api).then((updatedMedia) => {
                this.item.mediaId = targetId;
                this.item.media = updatedMedia;
            });
        },
        onDropMedia(dragData) {
            this.setMediaItem({targetId: dragData.id});
        },
        setMediaFromSidebar(mediaEntity) {
            this.item.mediaId = mediaEntity.id;
        },
        onUnlinkMedia() {
            this.item.mediaId = null;
        },
        onCloseModal() {
            this.mediaModalIsOpen = false;
        },
        onSelectionChanges(mediaEntity) {
            this.item.mediaId = mediaEntity[0].id;
            this.item.media = mediaEntity[0];
        },
        onOpenMediaModal() {
            this.mediaModalIsOpen = true;
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
            criteria.getAssociation('sections').addSorting(Criteria.sort('position'));
            criteria.addAssociation('sections.blocks');
            criteria.getAssociation('sections.blocks')
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
                                    merge(slot.config, cloneDeep(this.item.slotConfig[slot.id]));
                                }
                            });
                        });
                    });
                }
                this.updateCmsPageDataMapping();
                Shopware.State.commit('cmsPageState/setCurrentPage', cmsPage);
                return this.cmsPage;
            });
        },
        updateCmsPageDataMapping() {
            Shopware.State.commit('cmsPageState/setCurrentMappingEntity', this.entity);
            Shopware.State.commit(
                'cmsPageState/setCurrentMappingTypes',
                this.cmsService.getEntityMappingTypes(this.pageType),
            );
            Shopware.State.commit('cmsPageState/setCurrentDemoEntity', this.item);
        },
        getCmsPageOverrides() {
            if (this.cmsPage === null) {
                return null;
            }
            this.deleteSpecifcKeys(this.cmsPage.sections);
            const changesetGenerator = new ChangesetGenerator();
            const {changes} = changesetGenerator.generate(this.cmsPage);
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
    }
});
