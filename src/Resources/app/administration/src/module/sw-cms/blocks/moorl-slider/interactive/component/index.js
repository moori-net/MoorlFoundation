import template from './index.html.twig';
import './index.scss';

const {Component} = Shopware;

Component.register('sw-cms-block-moorl-interactive-slider', {
    template,

    inject: [
        'repositoryFactory'
    ],

    data() {
        return {
            showConfigurationModal: false,
            activeSlot: null,
            block: null
        };
    },

    computed: {
        moorlFoundation() {
            return MoorlFoundation;
        },

        slotRepository() {
            return this.repositoryFactory.create('cms_slot');
        },

        slotStyle() {
            return {
                'min-height': this.block.customFields.moorl_slider_interactive.itemHeight,
            }
        }
    },

    created() {
        this.block = this.$parent.block;

        if (!this.block.customFields) {
            this.$set(this.block, 'customFields', {});
        }

        if (!this.block.customFields.moorl_slider_interactive) {
            this.$set(this.block.customFields, 'moorl_slider_interactive', {
                slots: 1,
                itemWidth: '100%',
                itemHeight: '340px',
                gapSize: '0px',
                speed: 500,
                autoplayTimeout: 500,
                autoplay: true,
                autoplayHoverPause: true,
                navigation: false,
                animateIn: null,
                animateOut: null
            });
        }

        this.activeSlot = this.block.slots.last().id
    },

    methods: {
        addSlot() {
            let slotCount = this.block.slots.length;
            let char = String.fromCharCode(slotCount + 97);

            const slot = this.slotRepository.create();
            slot.blockId = this.block.id;
            slot.slot = `slot-${char}`;
            slot.type = 'moorl-replacer';

            this.block.slots.add(slot);

            this.activeSlot = slot.id

            this.$parent.$parent.$forceUpdate();
        },

        removeSlot() {
            let slotCount = this.block.slots.length;

            if (slotCount < 2) {
                return;
            }

            let slotId = this.block.slots.last().id;

            this.block.slots.remove(slotId);

            this.activeSlot = this.block.slots.last().id

            this.$parent.$parent.$forceUpdate();
        }
    }
});
