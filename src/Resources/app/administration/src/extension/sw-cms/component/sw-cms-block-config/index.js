import template from './sw-cms-block-config.html.twig';

const {Component} = Shopware;

Component.override('sw-cms-block-config', {
    template,

    computed: {
        moorlIsUnlocked() {
            return Shopware.Store.get('moorlFoundationState').unlocked;
        }
    }
});
