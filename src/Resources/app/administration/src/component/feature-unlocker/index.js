import template from './index.html.twig';
import './index.scss';

const {Component, Mixin} = Shopware;

Component.register('moorl-feature-unlocker', {
    template,

    shortcuts: {
        'SYSTEMKEY+m': 'openModal',
    },

    inject: ['acl'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            open: false
        };
    },

    computed: {
        moorlIsUnlocked() {
            return Shopware.State.get('moorlFoundationState').unlocked;
        },
        unlockInfoSeen() {
            return Shopware.State.get('moorlFoundationState').unlockInfoSeen;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (!this.acl.can('system.system_config')) {
                return;
            }

            Shopware.State.commit('moorlFoundationState/setUnlocked', false);

            if (!localStorage.getItem('moorl-foundation-welcome-seen')) {
                this.createNotificationInfo({
                    message: this.$tc('moorl-feature-unlocker.notifications.welcome'),
                });

                localStorage.setItem('moorl-foundation-welcome-seen', '1');
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Alt' || (event.key === 'm' && event.altKey)) {
                    event.preventDefault();
                }
            });
        },

        openModal() {
            if (!this.acl.can('system.system_config')) {
                return;
            }

            this.open = true;
        },

        toggleUnlocked() {
            if (this.moorlIsUnlocked) {
                localStorage.removeItem('moorl-foundation-unlocked');
                Shopware.State.commit('moorlFoundationState/setUnlocked', false);
                this.createNotificationInfo({
                    message: this.$tc('moorl-feature-unlocker.notifications.locked'),
                });
                return false;
            } else {
                localStorage.setItem('moorl-foundation-unlocked', '1');
                Shopware.State.commit('moorlFoundationState/setUnlocked', true);
                this.createNotificationInfo({
                    message: this.$tc('moorl-feature-unlocker.notifications.unlocked'),
                });
                return true;
            }
        },

        closeModal() {
            this.open = false;
        },

        unlock() {
            this.open = false;
        },
    },
});