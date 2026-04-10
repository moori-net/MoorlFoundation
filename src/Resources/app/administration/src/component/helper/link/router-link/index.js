import template from './index.html.twig';

Shopware.Component.register('moorl-router-link', {
    template,

    props: {
        path: {
            type: String,
            required: false,
        },
        params: {
            type: Object,
            required: false,
            default: () => null
        },
        plugin: {
            type: String,
            required: false,
        }
    },

    computed: {
        routerLink() {
            if (this.plugin) {
                return {
                    name: 'sw.extension.config',
                    params: { namespace: this.plugin }
                };
            }

            return {
                name: this.path,
                params: this.params
            };
        },

        label() {
            if (this.plugin) {
                return this.$tc('moorl-router-link.configLabel');
            }

            return this.$tc('global.default.add');
        },
    },
});
