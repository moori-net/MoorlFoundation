const {Criteria} = Shopware.Data;

import template from './index.html.twig';

Shopware.Component.register('moorl-abstract-page-detail', {
    template,

    inject: [
        'repositoryFactory',
        'customFieldDataProviderService',
        'seoUrlService'
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
            showConfirmDeleteModal: false,
            customFieldSets: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.itemName),
        };
    },

    computed: {
        itemHelper() {
            return new MoorlFoundation.ItemHelper({
                componentName: this.componentName,
                entity: this.entity,
                tc: this.$tc
            });
        },

        componentName() {
            return this.$options.name;
        },

        itemName() {
            for (const property of ['name', 'label', 'key', 'technicalName']) {
                if (this.item[property] !== undefined) {
                    return this.item[property];
                }
            }

            return this.$tc('global.default.add');
        },

        itemRepository() {
            return this.repositoryFactory.create(this.entity);
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },

        itemCriteria() {
            const itemCriteria = new Criteria();

            this.itemHelper.getAssociations().forEach(association => {
                itemCriteria.addAssociation(association);
            });

            if (this.itemHelper.hasSeoUrls()) {
                itemCriteria.getAssociation('seoUrls').addFilter(Criteria.equals('isCanonical', true));
            }

            return itemCriteria;
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
                console.error(`${this.componentName} has no entity`);
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

        async loadCustomFieldSets() {
            this.customFieldSets = await this.customFieldDataProviderService.getCustomFieldSets(this.entity);
        },

        async loadItem() {
            this.isLoading = true;

            if (this.isNewItem) {
                this.item = await this.itemRepository.create(Shopware.Context.api);
                this.onItemLoaded();

                this.isLoading = false;
                return;
            }

            try {
                this.item = await this.itemRepository.get(this.itemId, Shopware.Context.api, this.itemCriteria);
                this.onItemLoaded();
            } catch (error) {
                this.createNotificationError({ message: error.message });
            } finally {
                this.isLoading = false;
            }
        },

        onItemLoaded() {},

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
                    name: `${this.itemName} [${this.$tc('global.default.duplicate')}]`,
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
