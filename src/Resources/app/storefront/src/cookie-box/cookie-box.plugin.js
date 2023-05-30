import Plugin from 'src/plugin-system/plugin.class';
import {COOKIE_CONFIGURATION_UPDATE} from 'src/plugin/cookie/cookie-configuration.plugin';
import CookieStorageHelper from 'src/helper/storage/cookie-storage.helper';

export default class MoorlCookieBoxPlugin extends Plugin {
    static options = {
        cookieKey: null,
        content: null
    };

    init() {
        this._acceptButton = this.el.querySelector('button');
        this._registerEvents();

        if (!CookieStorageHelper.getItem(this.options.cookieKey)) {
            this.el.style.display = "flex";
        } else if (this.options.content) {
            this.el.parentElement.innerHTML = this.options.content;

            window.PluginManager.initializePlugins();
        }
    }

    _registerEvents() {
        document.$emitter.subscribe(COOKIE_CONFIGURATION_UPDATE, (updatedCookies) => {});

        this._acceptButton.addEventListener('click', event => {
            CookieStorageHelper.setItem(this.options.cookieKey, "1");
            window.location.reload();
        });
    }
}
