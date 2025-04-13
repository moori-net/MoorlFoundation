import template from './index.html.twig';
import './index.scss';

const { Component, Context } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Component.register('moorl-properties-card', {
    template,

    compatConfig: Shopware.compatConfig,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    emits: ['update:entityCollection'],

    props: {
        entityCollection: {
            type: Array,
            required: true
        },
        label: {
            type: String,
            required: false,
            default: "",
        }
    },

    data() {
        return {
            groupIds: [],
            properties: [],
            isPropertiesLoading: false,
            searchTerm: null,
            showAddPropertiesModal: false,
            newProperties: [],
            propertiesAvailable: true,
        };
    },

    computed: {
        propertyGroupRepository() {
            return this.repositoryFactory.create('property_group');
        },

        propertyOptionRepository() {
            return this.repositoryFactory.create('property_group_option');
        },

        propertyGroupCriteria() {
            const criteria = new Criteria(1, 10);

            criteria.addSorting(Criteria.sort('name', 'ASC', false));
            criteria.addFilter(Criteria.equalsAny('id', this.groupIds));

            if (this.searchTerm) {
                criteria.setTerm(this.searchTerm);
            }

            const optionIds = this.itemProperties.getIds();

            criteria.getAssociation('options').addFilter(Criteria.equalsAny('id', optionIds));
            criteria.addFilter(Criteria.equalsAny('options.id', optionIds));

            return criteria;
        },

        propertyColumns() {
            return [
                {
                    property: 'name',
                    label: 'sw-product.properties.columnProperty',
                    sortable: false,
                    routerLink: 'sw.property.detail',
                },
                {
                    property: 'values',
                    label: 'sw-product.properties.columnValue',
                    sortable: false,
                },
            ];
        },

        itemProperties() {
            return this.entityCollection;
        },

        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },

        itemHasProperties() {
            return this.groupIds.length > 0;
        },
    },

    watch: {
        itemProperties: {
            immediate: true,
            handler(newValue) {
                if (!newValue) {
                    return;
                }
                this.getGroupIds();
                this.getProperties();
            },
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.checkIfPropertiesExists();
        },

        getGroupIds() {
            this.groupIds = this.itemProperties.reduce((accumulator, { groupId }) => {
                if (accumulator.indexOf(groupId) < 0) {
                    accumulator.push(groupId);
                }

                return accumulator;
            }, []);
        },

        getProperties() {
            if (this.groupIds.length <= 0) {
                this.properties = [];
                this.searchTerm = null;
                return Promise.resolve();
            }

            this.isPropertiesLoading = true;
            return this.propertyGroupRepository
                .search(this.propertyGroupCriteria, Context.api)
                .then((properties) => {
                    this.properties = properties;
                })
                .catch(() => {
                    this.properties = [];
                })
                .finally(() => {
                    this.isPropertiesLoading = false;
                });
        },

        onDeletePropertyValue(propertyValue) {
            this.itemProperties.remove(propertyValue.id);
        },

        onDeleteProperty(property) {
            this.$refs.entityListing.deleteId = null;

            this.$nextTick(() => {
                this.itemProperties
                    .filter(({ groupId }) => {
                        return groupId === property.id;
                    })
                    .forEach(({ id }) => {
                        this.itemProperties.remove(id);
                    });

                this.$refs.entityListing.resetSelection();
            });
        },

        onDeleteProperties() {
            this.$refs.entityListing.showBulkDeleteModal = false;

            this.$nextTick(() => {
                const properties = { ...this.$refs.entityListing.selection };

                Object.values(properties).forEach((property) => {
                    property.options.forEach((value) => {
                        this.itemProperties.remove(value.id);
                    });
                });
                this.$refs.entityListing.resetSelection();
            });
        },

        onChangeSearchTerm(searchTerm) {
            this.searchTerm = searchTerm;
            return this.getProperties();
        },

        turnOnAddPropertiesModal() {
            if (!this.propertiesAvailable) {
                this.$router.push({ name: 'sw.property.index' });
            } else {
                this.updateNewProperties();
                this.showAddPropertiesModal = true;
            }
        },

        turnOffAddPropertiesModal() {
            this.showAddPropertiesModal = false;
            this.updateNewProperties();
        },

        updateNewProperties() {
            this.newProperties = new EntityCollection(
                this.itemProperties.source,
                this.itemProperties.entity,
                this.itemProperties.context,
                Criteria.fromCriteria(this.itemProperties.criteria),
                this.itemProperties,
                this.itemProperties.total,
                this.itemProperties.aggregations,
            );

            this.$emit('update:entityCollection', this.newProperties);
        },

        onCancelAddPropertiesModal() {
            this.turnOffAddPropertiesModal();
        },

        onSaveAddPropertiesModal(newProperties, callbackUpdateCurrentValues) {
            this.turnOffAddPropertiesModal();

            if (newProperties.length <= 0) {
                return;
            }

            if (typeof callbackUpdateCurrentValues === 'function') {
                callbackUpdateCurrentValues.bind(this)(newProperties);
            }
        },

        checkIfPropertiesExists() {
            this.propertyOptionRepository.search(new Criteria(1, 1)).then((res) => {
                this.propertiesAvailable = res.total > 0;
            });
        },
    },
});
