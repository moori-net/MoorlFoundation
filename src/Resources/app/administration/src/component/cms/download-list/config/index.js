const { Criteria, EntityCollection } = Shopware.Data;

import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-config-moorl-download-list', {
    template,

    mixins: [Shopware.Mixin.getByName('cms-element')],

    inject: ['repositoryFactory'],

    data() {
        return {
            downloadCollection: null,
        };
    },

    computed: {
        downloadRepository() {
            return this.repositoryFactory.create('media');
        },

        downloads() {
            if (
                this.element.data &&
                this.element.data.downloads &&
                this.element.data.downloads.length > 0
            ) {
                return this.element.data.downloads;
            }

            return null;
        },

        elementOptions() {
            return {
                layout: [
                    {
                        value: 'default',
                        label: 'sw-cms.elements.moorl-download-list.label.default',
                    },
                    {
                        value: 'minimal',
                        label: 'sw-cms.elements.moorl-download-list.label.minimal',
                    },
                ],
            };
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-download-list');

            this.downloadCollection = new EntityCollection(
                '/media',
                'media',
                Shopware.Context.api
            );

            if (!Array.isArray(this.element.config.downloads.value)) {
                return;
            }

            if (this.element.config.downloads.value.length === 0) {
                return;
            }

            const criteria = new Criteria();
            criteria.setIds(this.element.config.downloads.value);

            this.downloadRepository
                .search(criteria)
                .then((result) => {
                    this.downloadCollection = result;
                    this.onDownloadsChange();
                });
        },

        onDownloadsChange() {
            this.element.config.downloads.value =
                this.downloadCollection.getIds();
            this.element.data.downloads = this.downloadCollection;
            this.$emit('downloads-change');
        },
    },
});
