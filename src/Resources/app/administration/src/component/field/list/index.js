import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-list-field', {
    template,

    emits: ['update:value'],

    props: {
        value: {
            type: Array,
            required: false,
            default: () => [],
        },
        mapping: {
            type: Object,
            required: true,
        },
        min: {
            type: Number,
            required: true,
            default: 1,
        },
        titleAttribute : {
            type: String,
            required: false,
            default: 'name',
        },
        disabled: {
            type: Boolean,
            required: false,
            default: false,
        }
    },

    data() {
        return {
            activeEntry: -1
        };
    },

    computed: {
        currentValue: {
            get() {
                return this.value;
            },
            set(newValue) {
                this.$emit('update:value', newValue ?? null);
            },
        },

        canDelete() {
            return (this.value?.length ?? 0) > this.min;
        },
    },

    created() {
        this.ensureMinEntries();
    },

    watch: {
        value: {
            immediate: true,
            deep: false,
            handler() {
                this.ensureMinEntries();
            },
        },
        min() {
            this.ensureMinEntries();
        },
    },

    methods: {
        buildItem(index) {
            const item = {};
            Object.entries(this.mapping).forEach(([key, entry]) => {
                item[key] = entry.type === 'string' ? `${entry.value} #${index}` : entry.value;
            });
            return item;
        },

        ensureMinEntries() {
            const current = Array.isArray(this.value) ? this.value : [];
            const min = Math.max(0, Number(this.min) || 0);

            if (current.length >= min) return;

            const next = [...current];
            while (next.length < min) {
                // # beginnt bei 1
                next.push(this.buildItem(next.length + 1));
            }

            this.$emit('update:value', next);
        },

        addEntry() {
            const current = Array.isArray(this.value) ? this.value : [];
            const next = [...current, this.buildItem(current.length + 1)];
            this.$emit('update:value', next);
            this.activeEntry = current.length;
        },

        deleteEntry(index) {
            const current = Array.isArray(this.value) ? this.value : [];

            if (current.length <= this.min) return;

            const next = [...current];
            next.splice(index, 1);

            const min = Math.max(0, Number(this.min) || 0);
            while (next.length < min) {
                next.push(this.buildItem(next.length + 1));
            }

            this.$emit('update:value', next);
        },

        openEntry(index) {
            if (this.activeEntry === index) {
                this.activeEntry = -1;
            } else {
                this.activeEntry = index;
            }
        },

        canMoveUp(index) {
            const len = this.value?.length ?? 0;
            return !this.disabled && len > 1 && index > 0;
        },

        canMoveDown(index) {
            const len = this.value?.length ?? 0;
            return !this.disabled && len > 1 && index < len - 1;
        },

        moveEntry(index, direction) {
            const current = Array.isArray(this.value) ? this.value : [];
            if (current.length < 2) return;

            const targetIndex = direction === 'up' ? index - 1 : index + 1;

            if (targetIndex < 0 || targetIndex >= current.length) return;

            const next = [...current];
            [next[index], next[targetIndex]] = [next[targetIndex], next[index]];

            this.$emit('update:value', next);
        },

        moveUp(index) {
            this.moveEntry(index, 'up');
        },

        moveDown(index) {
            this.moveEntry(index, 'down');
        },
    },
});
