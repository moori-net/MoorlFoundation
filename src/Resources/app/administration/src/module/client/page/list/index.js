const { Criteria } = Shopware.Data;

import template from './index.html.twig';

Shopware.Component.register('moorl-client-list', {
    template,

    inject: ['repositoryFactory', 'numberRangeService', 'context'],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('listing'),
    ],

    data() {
        return {
            items: null,
            sortBy: 'name',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('moorl_client');
        },

        searchContext() {
            return {
                ...Shopware.Context.api,
                inheritance: true,
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
                    inlineEdit: 'boolean',
                },
                {
                    property: 'name',
                    dataIndex: 'name',
                    routerLink: 'moorl.client.detail',
                    label: this.$tc('moorl-client.properties.name'),
                    allowResize: true,
                    inlineEdit: 'string',
                },
                {
                    property: 'type',
                    dataIndex: 'type',
                    routerLink: 'moorl.client.detail',
                    label: this.$tc('moorl-client.properties.type'),
                    allowResize: true,
                },
            ];
        },
    },

    created() {
        // getList() will be called by listing (mixin)
    },

    methods: {
        async getList() {
            const criteria = new Criteria(this.page, this.limit, this.term);

            this.isLoading = true;

            try {
                const items = await this.repository.search(
                    criteria,
                    Shopware.Context.api
                );

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

        updateSelection() {},

        updateTotal({ total }) {
            this.total = total;
        },

        onDuplicate(reference) {
            const behavior = {
                cloneChildren: true,
                overwrites: {
                    name: `${reference.name} ${this.$tc('global.default.duplicate')}`,
                    locked: false,
                },
            };

            this.repository
                .clone(reference.id, behavior, Shopware.Context.api)
                .then((duplicate) => {
                    this.$router.push({
                        name: 'moorl.client.detail',
                        params: { id: duplicate.id },
                    });
                });
        },
    },
});
