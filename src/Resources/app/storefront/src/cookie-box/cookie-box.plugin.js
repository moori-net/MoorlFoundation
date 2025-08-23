const Plugin = window.PluginBaseClass;
import { COOKIE_CONFIGURATION_UPDATE } from 'src/plugin/cookie/cookie-configuration.plugin';
import CookieStorageHelper from 'src/helper/storage/cookie-storage.helper';

export default class MoorlCookieBoxPlugin extends Plugin {
    static options = {
        cookieKey: null,
        content: null,
        reload: true,
    };

    init() {
        this._acceptButton = this.el.querySelector(
            '.js-moorl-cookie-box-button button'
        );
        this._registerEvents();
        this._toggle();
    }

    _registerEvents() {
        document.$emitter.subscribe(
            COOKIE_CONFIGURATION_UPDATE,
            (updatedCookies) => {
                this._toggle();
            }
        );

        this._acceptButton.addEventListener('click', (event) => {
            CookieStorageHelper.setItem(this.options.cookieKey, '1', '30');
            if (this.options.reload) {
                window.location.reload();
                return;
            }

            const updatedCookies = { '${this.options.cookieKey}': true };
            document.$emitter.publish(
                COOKIE_CONFIGURATION_UPDATE,
                updatedCookies
            );

            this.el.style.display = 'none';
        });
    }

    _toggle() {
        if (!CookieStorageHelper.getItem(this.options.cookieKey)) {
            this.el.style.display = 'flex';
        } else if (this.options.content) {
            this.el.parentElement.innerHTML = this.options.content;

            window.PluginManager.initializePlugins();
        } else {
            this.el.style.display = 'none';
        }
    }
}
