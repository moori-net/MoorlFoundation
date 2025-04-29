import template from './index.html.twig';
import './index.scss';

const { Criteria } = Shopware.Data;

Shopware.Component.register('moorl-entity-select-field', {
    template,

    inject: ['repositoryFactory'],

    mixins: [Shopware.Mixin.getByName('notification')],

    emits: ['update:value'],

    props: {
        value: {
            type: String,
            required: false,
            default: undefined,
        },
        entity: {
            type: String,
            required: true,
        },
        defaultItem: {
            type: Object,
            required: false,
            default: {},
        },
        label: {
            type: String,
            required: false,
            default: undefined,
        },
        placeholder: {
            type: String,
            required: false,
            default: undefined,
        },
        helpText: {
            type: String,
            required: false,
            default: undefined,
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        }
    },

    data() {
        return {
            showEditModal: false,
            selectedItem: undefined
        };
    },

    computed: {
        fieldLabel() {
            return this.label ?? this.$tc(`global.entities.${this.entity}`);
        },

        vBind() {
            return {
                entity: this.entity,
                label: this.fieldLabel,
                helpText: this.helpText,
                placeholder: this.placeholder,
                disabled: this.disabled,
                showClearableButton: true
            };
        },

        currentValue: {
            get() {
                return this.value;
            },
            set(newValue) {
                this.$emit('update:value', newValue ?? null);
            },
        },

        itemRepository() {
            return this.repositoryFactory.create(this.entity);
        },

        itemCriteria() {
            const criteria  = new Criteria();
            const isUuid = /^[a-f0-9]{32}$/i;

            for (const [field, value] of Object.entries(this.defaultItem)) {
                if (typeof value === 'string' && isUuid.test(value)) {
                    continue;
                }

                criteria.addFilter(Criteria.equals(field, value));
            }

            return criteria;
        },
    },

    methods: {
        saveItem() {
            this.itemRepository
                .save(this.selectedItem, Shopware.Context.api)
                .then(() => {
                    this.currentValue = this.selectedItem.id;
                    this.showEditModal = false;
                })
                .catch((error) => {
                    this.createNotificationError({ message: error.message });
                });
        },

        async editItem() {
            if (this.currentValue !== null) {
                this.selectedItem = await this.itemRepository.get(this.currentValue, Shopware.Context.api);
            } else {
                this.selectedItem = this.itemRepository.create(Shopware.Context.api);

                this.selectedItem.id = Shopware.Utils.createId();

                Object.assign(this.selectedItem, this.defaultItem);
            }

            this.showEditModal = true;
        },
    }
});
