import template from './sw-cms-section-config.html.twig';

Shopware.Component.override('sw-cms-section-config', {
    template,

    computed: {
        moorlIsUnlocked() {
            return Shopware.Store.get('moorlFoundationState').unlocked;
        }
    }
});
