const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-page-list', {
    template,

    inject: [
        'repositoryFactory',
        'filterFactory'
    ],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('listing')
    ],

    data() {
        return {
            entity: null,
            properties: ['active', 'name'],
            mediaProperty: null,
            plugin: null, // Demo button
            snippetSrc: 'moorl-foundation',

            items: null,
            showImportModal: false,
            showExportModal: false,
            isLoading: true,
            activeFilterNumber: 0,
            sortBy: 'name',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        identifier() {
            return this.$options.name;
        },

        dateFilter() {
            return Shopware.Filter.getByName('date');
        },

        itemRepository() {
            return this.repositoryFactory.create(this.entity);
        },

        generatedAssociations() {
            const associations = [];

            if (this.mediaProperty) {
                associations.push(this.mediaProperty);
            }

            this.properties.forEach((property) => {
                let parts = property.split(".");
                parts.pop();

                if (parts.length > 0) {
                    const association = parts.join(".");
                    if (associations.indexOf(association) !== -1) {
                        associations.push(association);
                    }
                }
            });

            return associations;
        },

        itemCriteria() {
            const itemCriteria  = new Criteria(this.page, this.limit);
            this.naturalSorting = this.sortBy === 'priority';

            itemCriteria.setTerm(this.term);

            this.sortBy.split(',').forEach(sortBy => {
                itemCriteria.addSorting(Criteria.sort(sortBy, this.sortDirection, this.naturalSorting));
            });

            this.generatedAssociations.forEach(association => {
                itemCriteria.addAssociation(association);
            });

            return itemCriteria ;
        },

        generatedColumns() {
            const columns = [];
            const fields = Shopware.EntityDefinition.get(this.entity).properties;

            console.log(Shopware.EntityDefinition.get(this.entity));

            this.properties.forEach((property) => {
                if (fields[property] === undefined) {
                    console.error(`Property ${property} of ${this.identifier} not found in ${this.entity}`);
                    return;
                }

                const field = fields[property];
                const column = {
                    property: property,
                    dataIndex: property,
                    label: `${this.snippetSrc}.properties.${property}`,
                    allowResize: true,
                };

                if (['string'].indexOf(field.type) !== -1) {
                    column.inlineEdit = 'string';
                    column.align = 'left';
                    column.routerLink = this.getItemRoute('detail');
                }

                if (['int', 'float'].indexOf(field.type) !== -1) {
                    column.inlineEdit = 'number';
                    column.align = 'right';
                }

                if (['boolean'].indexOf(field.type) !== -1) {
                    column.inlineEdit = 'boolean';
                    column.align = 'center';
                }

                columns.push(column);
            });

            return columns;
        },

        columns() {
            return this.generatedColumns;
        }
    },

    methods: {
        async getList() {
            // This method is called by $route watcher of the listing mixin before created() is called!
            if (!this.entity) {
                console.error(`${this.identifier} has no entity`);
                return;
            }
            // Copies for listing mixin
            this.searchConfigEntity = this.entity;
            this.storeKey = `grid.filter.${this.entity}`;

            this.isLoading = true;

            let criteria = await Shopware.Service('filterService').mergeWithStoredFilters(this.storeKey, this.itemCriteria);

            criteria = await this.addQueryScores(this.term, criteria);

            if (!this.entitySearchable) {
                this.total = 0;
                this.isLoading = false;

                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            try {
                const response = await this.itemRepository.search(criteria);

                this.total = response.total;
                this.items = response
                this.isLoading = false;
            } catch {
                this.isLoading = false;
            }
        },

        getItemRoute(target) {
            let name = this.$route.name;
            let parts = name.split(".");
            parts.pop();
            parts.push(target ?? 'detail');
            return parts.join(".");
        },

        onCreateItem() {
            this.$router.push({
                name: this.getItemRoute('create')
            });
        },

        async onDuplicate(reference) {
            const behavior = {
                cloneChildren: true,
                overwrites: {
                    name: `${reference.name} [${this.$tc('global.default.duplicate')}]`,
                    locked: false
                }
            };

            const duplicate = await this.itemRepository.clone(reference.id, behavior, Shopware.Context.api);

            await this.$router.push({
                name: this.getItemRoute('detail'),
                params:{id: duplicate.id}
            });

            return Promise.resolve();
        },

        updateSelection(selection) {
            this.selection = selection;
        },

        updateTotal({total}) {
            this.total = total;
        },

        onCloseModal() {
            this.showImportModal = false;
            this.showExportModal = false;
            this.showModal = false;
        },

        onRefresh() {
            this.getList();
        },

        onChangeLanguage() {
            this.getList();
        },

        onImportModal() {
            this.showImportModal = true;
        },

        onExportModal() {
            this.showExportModal = true;
        }
    }
});
