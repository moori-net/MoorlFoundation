import template from './sw-cms-section-actions.html.twig';

Shopware.Component.override('sw-cms-section-actions', {
    template,

    computed: {
        moorlIsUnlocked() {
            return Shopware.Store.get('moorlFoundationState').unlocked;
        }
    }
});
