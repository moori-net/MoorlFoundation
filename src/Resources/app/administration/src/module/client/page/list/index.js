const {Component, Mixin, Context} = Shopware;
const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Component.register('moorl-client-list', {
    template,

    inject: [
        'repositoryFactory',
        'numberRangeService',
        'context'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            items: null,
            sortBy: 'name',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('moorl_client');
        },

        searchContext() {
            return {
                ...Context.api,
                inheritance: true
            };
        },

        columns() {
            return [
                {
                    property: 'active',
                    dataIndex: 'active',
                    routerLink: 'moorl.client.detail',
                    label: this.$tc('moorl-client.properties.active'),
                    allowResize: true,
                    inlineEdit: 'boolean'
                },
                {
                    property: 'name',
                    dataIndex: 'name',
                    routerLink: 'moorl.client.detail',
                    label: this.$tc('moorl-client.properties.name'),
                    allowResize: true,
                    inlineEdit: 'string'
                },
                {
                    property: 'type',
                    dataIndex: 'type',
                    routerLink: 'moorl.client.detail',
                    label: this.$tc('moorl-client.properties.type'),
                    allowResize: true
                }
            ]
        }
    },

    created() {
        // getList() will be called by listing (mixin)
    },

    methods: {
        async getList() {
            const criteria = new Criteria(this.page, this.limit, this.term);

            this.isLoading = true;

            try {
                const items = await this.repository.search(criteria, Shopware.Context.api);

                this.total = items.total;
                this.isLoading = false;
                this.items = items;
                this.selection = {};
            } catch {
                this.isLoading = false;
            }
        },

        changeLanguage() {
            this.getList();
        },

        updateSelection() {
        },

        updateTotal({total}) {
            this.total = total;
        },

        onDuplicate(reference) {
            this.repository.clone(reference.id, Shopware.Context.api, {
                name: `${reference.name} ${this.$tc('sw-product.general.copy')}`,
                locked: false
            }).then((duplicate) => {
                this.$router.push({name: 'moorl.client.detail', params: {id: duplicate.id}});
            });
        }
    }
});
