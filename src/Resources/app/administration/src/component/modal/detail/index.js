import template from './index.html.twig';

const {Criteria} = Shopware.Data;

Shopware.Component.register('moorl-modal-detail', {
    template,

    inject: ['repositoryFactory'],

    mixins: [Shopware.Mixin.getByName('notification')],

    emits: ['update:value'],

    props: {
        value: {
            type: String,
            required: false,
            default: null,
        },
        entity: {
            type: String,
            required: true,
        },
        componentName: {
            type: String,
            required: true,
        },
        defaultItem: {
            type: Object,
            required: false,
            default: {},
        }
    },

    data() {
        return {
            item: undefined,
            showEditModal: false,
            isSaveSuccessful: false,
            isLoading: true,
        };
    },

    computed: {
        itemHelper() {
            return new MoorlFoundation.ItemHelper({
                componentName: this.componentName,
                entity: this.entity
            });
        },

        itemRepository() {
            return this.repositoryFactory.create(this.entity);
        },

        itemCriteria() {
            const itemCriteria = new Criteria();

            this.itemHelper.getAssociations().forEach(association => {
                itemCriteria.addAssociation(association);
            });

            return itemCriteria;
        },

        itemName() {
            if (this.item === undefined) {
                return this.$tc('global.default.add');
            }

            for (const property of ['name', 'label', 'key', 'technicalName']) {
                if (this.item[property] !== undefined) {
                    return this.item[property];
                }
            }

            return this.$tc('global.default.add');
        }
    },

    created() {
        this.loadItem();
    },

    methods: {
        async onSaveItem() {
            this.isSaveSuccessful = false;

            try {
                await this.itemRepository.save(this.item);
                this.isSaveSuccessful = true;
                this.$emit('update:value', this.item.id);
                this.onCloseModal();
            } catch(error) {
                console.error(error);
                this.createNotificationError({ message: error.message });
            }

            return Promise.resolve();
        },

        async loadItem() {
            if (this.value !== null) {
                this.item = await this.itemRepository.get(this.value, Shopware.Context.api, this.itemCriteria);
            } else {
                this.item = this.itemRepository.create(Shopware.Context.api);
                this.item.id = Shopware.Utils.createId();
                Object.assign(this.item, this.defaultItem);
            }

            this.isLoading = false;
        },

        onCloseModal() {
            this.isLoading = true;

            setTimeout(() => {
                this.$emit('close');
            }, 50);
        }
    }
});
