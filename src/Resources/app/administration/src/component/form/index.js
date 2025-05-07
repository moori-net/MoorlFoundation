import template from './index.html.twig';
import './index.scss';

const fieldsets = MoorlFoundation.fieldsetsConfig;

Shopware.Component.register('moorl-form', {
    template,

    mixins: [Shopware.Mixin.getByName('moorl-form')],

    props: {
        fieldset: {
            type: [String, Array],
            default: undefined
        },
    },

    computed: {
        masterMapping() {
            if (!this.fieldset && !this.mapping) {
                return undefined;
            }

            const masterMapping = {};
            const sets = Array.isArray(this.fieldset) ? this.fieldset : [this.fieldset];

            for (const set of sets) {
                if (!set) continue;
                if (!fieldsets[set]) {
                    console.error(`[moorl-form] Fieldset "${set}" not defined`);
                    continue;
                }
                Object.assign(masterMapping, fieldsets[set]);
            }

            return Object.assign(masterMapping, this.mapping ?? {});
        }
    }
});
