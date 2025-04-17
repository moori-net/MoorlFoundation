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
            isSaveSuccessful: false
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
        goToRoute(target, id) {
            let name = this.$route.name;
            let parts = name.split(".");
            parts.pop();
            parts.push(target);
            name = parts.join(".");

            if (id === undefined) {
                this.$router.push({name});
            } else {
                this.$router.push({name, params: {id}});
            }
        },

        createdComponent() {
            if (!this.entity) {
                console.error(`${this.currentComponentName} has no entity`);
                return;
            }

            this.loadItem();
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
                this.createNotificationError({
                    message: this.$tc(error.message),
                });
            } finally {
                this.isLoading = false;
            }
        },

        async onSaveItem() {
            if (this.item === null) {
                return;
            }

            this.isSaveSuccessful = false;
            this.isLoading = true;

            try {
                await this.updateSeoUrls();
                await this.itemRepository.save(this.item);

                if (this.isNewItem) {
                    this.goToRoute('detail', this.item.id);
                    return;
                }

                await this.loadItem();

                this.isSaveSuccessful = true;
            } catch {
                this.createNotificationError({
                    message: this.$tc('sw-settings-state-machine.notification.errorMessage'),
                });
            } finally {
                this.isLoading = false;
            }
        },

        async onDuplicateItem() {
            const behavior = {
                cloneChildren: true,
                overwrites: {
                    name: `${this.identifier} [${this.$tc('global.default.duplicate')}]`,
                    locked: false
                }
            };

            this.itemRepository.clone(this.itemId, behavior, Shopware.Context.api).then(
                (duplicate) => {this.goToRoute('detail', duplicate.id);}
            );
        },

        updateSeoUrls() {
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
