const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-moorl-foundation-listing', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            entity: 'moorl_magazine_article',
            elementName: 'moorl-foundation-listing'
        };
    },

    computed: {
        moorlFoundation() {
            return MoorlFoundation;
        },

        sortingCriteria() {
            const criteria = new Criteria
            criteria.addFilter(Criteria.equals('entity', this.entity));
            return criteria;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig(this.elementName);
            this.initElementData(this.elementName);
        },
    }
});
