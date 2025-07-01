import template from './sw-cms-page-form.html.twig';

Shopware.Component.override('sw-cms-page-form', {
    template,

    computed: {
        moorlIsUnlocked() {
            return Shopware.Store.get('moorlFoundationState').unlocked;
        },

        slotPositions() {
            const slotPositions = this.$super('slotPositions');

            slotPositions['slot-a'] = 1;
            slotPositions['slot-b'] = 2;
            slotPositions['slot-c'] = 3;
            slotPositions['slot-d'] = 4;
            slotPositions['slot-e'] = 5;
            slotPositions['slot-f'] = 6;
            slotPositions['slot-g'] = 7;
            slotPositions['slot-h'] = 8;
            slotPositions['slot-i'] = 9;
            slotPositions['slot-j'] = 10;

            return slotPositions;
        },
    },
});
