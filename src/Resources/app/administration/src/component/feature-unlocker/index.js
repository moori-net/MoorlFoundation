import template from './index.html.twig';
import './index.scss';

const {Component, Mixin} = Shopware;

Component.register('moorl-feature-unlocker', {
    template,

    shortcuts: {
        'SYSTEMKEY+m': 'openModal',
    },

    inject: [
        'cacheApiService',
        'acl',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            open: false,
        };
    },

    computed: {
        moorlIsUnlocked() {
            return Shopware.State.get('moorlFoundationState').unlocked;
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

            this.createNotificationInfo({
                message: this.$tc('moorl-feature-unlocker.notifications.welcome'),
            });

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

            if (this.moorlIsUnlocked) {
                this.createNotificationInfo({
                    message: this.$tc('moorl-feature-unlocker.notifications.locked'),
                });
            } else {
                this.createNotificationInfo({
                    message: this.$tc('moorl-feature-unlocker.notifications.unlocked'),
                });

                this.open = true;
            }

            Shopware.State.commit('moorlFoundationState/toggleUnlocked');
        },

        closeModal() {
            this.open = false;
        },

        unlock() {
            this.open = false;
        },
    },
});
