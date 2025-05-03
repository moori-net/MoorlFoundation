import template from './index.html.twig';

Shopware.Component.register('sw-cms-el-config-moorl-location', {
    template,

    props: {
        element: {
            type: Object,
            required: false,
            default: null,
        },
    },

    mixins: [Shopware.Mixin.getByName('cms-element')],

    inject: ['repositoryFactory'],

    computed: {
        elementOptions() {
            return {
                osmOptions: [
                    { value: 'scrollWheelZoom', label: 'scrollWheelZoom' },
                    { value: 'dragging', label: 'dragging' },
                    { value: 'tap', label: 'tap' },
                    { value: 'autoPan', label: 'autoPan' },
                    { value: 'autoClose', label: 'autoClose' },
                    { value: 'scrollTo', label: 'scrollTo' },
                    { value: 'flyTo', label: 'flyTo' },
                    { value: 'fitBounds', label: 'fitBounds' },
                    { value: 'gestureHandling', label: 'gestureHandling' },
                ],
            };
        },

        markerRepository() {
            return this.repositoryFactory.create('moorl_marker');
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!Object.keys(this.element.config).length) {
                this.initElementConfig('moorl-location');
            }
        },
    },
});
