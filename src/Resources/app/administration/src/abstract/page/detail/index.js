const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-page-detail', {
    template,

    inject: [
        'repositoryFactory',
        'customFieldDataProviderService',
    ],

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder')
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },

    data() {
        return {
            entity: null,
            item: {},
            isLoading: true,
            isSaveSuccessful: false,
            showConfirmDeleteModal: false
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    computed: {
        currentComponentName() {
            return this.$options.name;
        },

        itemRepository() {
            return this.repositoryFactory.create(this.entity);
        },

        itemCriteria() {
            console.warn(`${this.currentComponentName} missing computed itemCriteria`);
            return new Criteria();
        },

        identifier() {
            if (this.item?.name) {
                return this.item.name;
            }

            return this.$tc('global.default.add');
        },

        translatable() {
            return !!this.item?.translated;
        },

        isNewItem() {
            return !this.itemId;
        },

        itemId() {
            return this.$route.params.id;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!this.entity) {
                console.error(`${this.currentComponentName} has no entity`);
                return;
            }

            this.loadItem();
        },

        async goToRoute(target, id) {
            let name = this.$route.name;
            let parts = name.split(".");
            parts.pop();
            parts.push(target);
            name = parts.join(".");

            if (id === undefined) {
                await this.$router.push({name});
            } else {
                await this.$router.push({name, params: {id}});
            }

            return Promise.resolve();
        },

        onChangeLanguage() {
            this.loadItem();
        },

        async loadItem() {
            this.isLoading = true;

            if (this.isNewItem) {
                this.item = await this.itemRepository.create(Shopware.Context.api);
                this.isLoading = false;
                return;
            }

            try {
                this.item = await this.itemRepository.get(this.itemId, Shopware.Context.api, this.itemCriteria);
            } catch (error) {
                this.createNotificationError({ message: error.message });
            } finally {
                this.isLoading = false;
            }
        },

        async onSaveItem() {
            this.isSaveSuccessful = false;
            this.isLoading = true;

            try {
                await this.updateSeoUrls();
                await this.itemRepository.save(this.item);

                if (this.isNewItem) {
                    await this.goToRoute('detail', this.item.id);
                    return Promise.resolve();
                }

                this.isSaveSuccessful = true;
            } catch(error) {
                this.createNotificationError({ message: error.message });
            }

            this.isLoading = false;
            return Promise.resolve();
        },

        async onDuplicateItem() {
            await this.onSaveItem();

            this.isLoading = true;

            const behavior = {
                cloneChildren: true,
                overwrites: {
                    name: `${this.identifier} [${this.$tc('global.default.duplicate')}]`,
                    locked: false
                }
            };

            const duplicate = await this.itemRepository.clone(this.itemId, behavior, Shopware.Context.api);

            await this.goToRoute('detail', duplicate.id);
            await this.loadItem();

            return Promise.resolve();
        },

        onConfirmDeleteItem() {
            this.isLoading = true;

            this.itemRepository.delete(this.itemId).then(() => {
                this.onCancel();
            });
        },

        async updateSeoUrls() {
            if (!Shopware.Store.list().includes('swSeoUrl')) {
                return Promise.resolve();
            }

            const seoUrls = Shopware.Store.get('swSeoUrl').newOrModifiedUrls;

            return Promise.all(
                seoUrls.map((seoUrl) => {
                    if (seoUrl.seoPathInfo) {
                        seoUrl.isModified = true;
                        return this.seoUrlService.updateCanonicalUrl(seoUrl, seoUrl.languageId);
                    }

                    return Promise.resolve();
                }),
            );
        },

        onCancel() {
            this.goToRoute('list');
        },
    }
});
