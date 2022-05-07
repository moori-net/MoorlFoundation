const {Component, Mixin} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;

import template from './index.html.twig';

Component.register('sw-cms-el-config-moorl-download-list', {
    template,

    props: {
        element: {
            type: Object,
            required: false,
            default: null,
        }
    },

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            downloadCollection: null
        };
    },

    computed: {
        downloadRepository() {
            return this.repositoryFactory.create('media');
        },

        downloads() {
            if (this.element.data && this.element.data.downloads && this.element.data.downloads.length > 0) {
                return this.element.data.downloads;
            }

            return null;
        },

        elementOptions() {
            return {
                layout: [
                    {value: 'default', label: 'sw-cms.elements.moorl-download-list.label.default'},
                    {value: 'minimal', label: 'sw-cms.elements.moorl-download-list.label.minimal'}
                ]
            };
        },

        downloadSearchCriteria() {
            const criteria = new Criteria(1, 25);
            return criteria;
        },

        downloadSearchContext() {
            const context = Object.assign({}, Shopware.Context.api);
            context.inheritance = true;

            return context;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-download-list');
            this.initElementData('moorl-download-list');

            this.downloadCollection = new EntityCollection('/media', 'media', Shopware.Context.api);

            if (this.element.config.downloads.value.length <= 0) {
                return;
            }

            const criteria = new Criteria(1, 25);
            criteria.setIds(this.element.config.downloads.value);

            this.downloadRepository.search(criteria, this.downloadSearchContext)
                .then(result => {
                    this.downloadCollection = result;
                    this.onDownloadsChange();
                });
        },

        onDownloadsChange() {
            this.element.config.downloads.value = this.downloadCollection.getIds();
            this.$set(this.element.data, 'downloads', this.downloadCollection);
            this.$emit('downloads-change');
        }
    }
});
