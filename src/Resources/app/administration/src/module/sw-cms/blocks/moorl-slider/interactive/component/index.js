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
                'min-height': this.block.customFields.moorl_interactive_slider.itemHeight,
            }
        }
    },

    created() {
        this.block = this.$parent.$parent.block;

        if (!this.block.customFields) {
            this.$set(this.block, 'customFields', {});
        }

        if (!this.block.customFields.moorl_interactive_slider) {
            this.$set(this.block.customFields, 'moorl_interactive_slider', {
                slots: 1,
                itemWidth: '100%',
                itemHeight: '340px',
                gapSize: '0px',
                speed: 800,
                autoplayTimeout: 2000,
                autoplay: true,
                autoplayHoverPause: true,
                navigation: false,
                animateIn: null,
                animateOut: null
            });
        }

        this.sanitizeSlots();

        this.activeSlot = this.block.slots.last().id
    },

    methods: {
        sanitizeSlots() {
            this.block.slots.sort((a, b) => a.slot > b.slot && 1 || -1);
            this.block.slots.forEach(function (element, index) {
                let char = String.fromCharCode(index + 97);
                element.slot = `slot-${char}`;
            });
        },

        addSlot() {
            let slotCount = this.block.slots.length;
            if (slotCount > 25) {
                return;
            }

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
