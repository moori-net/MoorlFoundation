const {Component, Mixin} = Shopware;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-moorl-foundation-listing', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            entity: 'moorl_magazine_article',
            elementName: 'moorl-magazine',
            defaultCriteria: null,
        };
    },

    computed: {
        moorlFoundation() {
            return MoorlFoundation;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-magazine');
            this.initElementData('moorl-magazine');

        },
    }
});
