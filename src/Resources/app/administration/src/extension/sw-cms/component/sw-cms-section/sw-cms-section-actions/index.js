import template from './sw-cms-section-actions.html.twig';

const {Component} = Shopware;

Component.override('sw-cms-section-actions', {
    template,

    computed: {
        moorlIsUnlocked() {
            return Shopware.Store.get('moorlFoundationState').unlocked;
        }
    }
});
