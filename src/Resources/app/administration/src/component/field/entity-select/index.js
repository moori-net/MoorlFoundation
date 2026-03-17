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
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        },
        variant: {
            type: String,
            required: false,
            default: undefined,
        },
        showEditButton: {
            type: Boolean,
            required: false,
            default: true,
        },
    },

    data() {
        return {
            showEditModal: false,
            selectedItem: undefined
        };
    },

    computed: {
        fieldLabel() {
            if (this.variant === 'small') {
                return undefined;
            }

            return this.label ?? this.$tc(`global.entities.${this.entity}`);
        },

        vBind() {
            return {
                ...this.$attrs,
                label: this.fieldLabel,
                variant: this.variant,
                entity: this.entity,
                disabled: this.disabled
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

        routerLink() {
            const routerLink = MoorlFoundation.RouteHelper.getRouterLinkByEntity(
                this.entity,
                this.currentValue ? 'detail' : 'create'
            );

            if (this.routeExists(routerLink)) {
                return routerLink;
            }

            return MoorlFoundation.RouteHelper.getRouterLinkByEntity(this.entity, 'index');
        },
    },

    methods: {
        routeExists(routerLink) {
            return this.$router.getRoutes().some(route => route.name === routerLink);
        },

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

        editItem() {
            this.showEditModal = true;
        },

        openNewTab() {
            const routeData = this.$router.resolve({ name: this.routerLink, params: { id: this.currentValue } });
            window.open(routeData.href, '_blank');
        }
    }
});
