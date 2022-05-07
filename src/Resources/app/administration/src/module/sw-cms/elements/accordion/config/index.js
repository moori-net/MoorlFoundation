const { Component, Mixin } = Shopware;

import draggable from 'vuedraggable';
import template from './index.html.twig';
import './index.scss';

Component.register('sw-cms-el-config-moorl-accordion', {
    template,

    inject: ['repositoryFactory'],

    components: {
        draggable,
    },

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            snippetPrefix: 'sw-cms.elements.moorl-accordion.',
            sortable: null,
            drag: false
        };
    },

    computed: {
        mediaRepository() {
            return this.repositoryFactory.create('media');
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('moorl-accordion');
        },

        addEntry() {
            this.element.config.entries.value.push({
                order: 1,
                name: 'This is my entry',
                content: '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua</p>'
            });
        },

        removeEntry(e, index) {
            this.$delete(this.element.config.entries.value, index);

            if (this.element.config.entries.value.length === 0) {
                this.addEntry();
            }
        },

        dragChoose(e) {
            for (let i in this.$refs) {
                if (this.$refs.hasOwnProperty(i)) {
                    this.$refs[i][0].expanded = false;
                }
            }
        },

        dragStart(e) {
            this.drag = true;
        },

        dragEnd(e) {
            this.drag = false;
        },
    }
});
