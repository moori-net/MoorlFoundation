import template from './index.html.twig';
import './index.scss';

Shopware.Component.register('moorl-cms-element-config-form', {
    template,

    mixins: [Shopware.Mixin.getByName('moorl-form')],

    props: {
        cmsElementMapping: {
            type: Object,
            required: true,
        }
    },

    computed: {
        fieldModels() {
            return new Proxy({}, {
                get: (_, prop) => {
                    if (this.item?.[prop] === undefined) {
                        console.error(prop);
                    }

                    return this.item?.[prop].value;
                },
                set: (_, prop, value) => {
                    this.item[prop].value = value;
                    return true;
                }
            });
        }
    },

    methods: {
        async loadCustomData() {
            this.formBuilderHelper.masterMapping = this.cmsElementMapping;
        },
    }
});
