import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-modal-detail', {
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
        itemName() {
            for (const property of ['name', 'label', 'key', 'technicalName']) {
                if (this.item[property] !== undefined) {
                    return this.item[property];
                }
            }

            return this.$tc('global.default.add');
        },

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

        modalStruct() {
            return this.formBuilderHelper.buildPageStruct();
        },

        defaultTab() {
            return this.modalStruct.tabs[0].id;
        },

        fieldModels() {
            return new Proxy({}, {
                get: (_, prop) => {
                    return this.item.extensions?.[prop] ?? this.item?.[prop];
                },
                set: (_, prop, value) => {
                    if (this.item.extensions?.hasOwnProperty(prop)) {
                        this.item.extensions[prop] = value;
                    } else {
                        this.item[prop] = value;
                    }
                    return true;
                }
            });
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
        },

        onCloseModal() {
            this.$emit('modal-close');
        }
    }
});
