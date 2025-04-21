import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-page-detail', {
    template,

    mixins: [
        Shopware.Mixin.getByName('notification'),
        Shopware.Mixin.getByName('placeholder')
    ],

    inject: [
        'customFieldDataProviderService',
    ],

    props: {
        entity: {
            type: String,
            required: true,
        },
        componentName: {
            type: String,
            required: true
        },
        item: {
            type: Object,
            required: true,
        }
    },

    data() {
        return {
            customFieldSets: null,
        };
    },

    computed: {
        formBuilderHelper() {
            return new MoorlFoundation.FormBuilderHelper({
                item: this.item,
                entity: this.entity,
                tc: this.$tc,
                componentName: this.componentName
            });
        },

        translationHelper() {
            return this.formBuilderHelper.translationHelper;
        },

        pageStruct() {
            return this.formBuilderHelper.buildPageStruct();
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async loadCustomFieldSets() {
            if (this.item.customFields === undefined) {
                return Promise.resolve();
            }

            this.customFieldSets = await this.customFieldDataProviderService
                .getCustomFieldSets(this.entity);
        },

        async createdComponent() {
            await this.loadCustomFieldSets();

            this.formBuilderHelper.customFieldSets = this.customFieldSets;
        }
    }
});
