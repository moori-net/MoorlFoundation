import template from './sw-cms-stage-section-selection.html.twig';
import './sw-cms-stage-section-selection.scss';

Shopware.Component.override('sw-cms-stage-section-selection', {
    template,

    computed: {
        moorlIsUnlocked() {
            return Shopware.Store.get('moorlFoundationState').unlocked;
        }
    },
});
