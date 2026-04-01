import Plugin from 'src/plugin-system/plugin.class';
import LoadingIndicatorUtil from 'src/utility/loading-indicator/loading-indicator.util';

export default class MoorlAjaxWidgetPlugin extends Plugin {
    static options = {
        url: null,
        method: 'POST',
        silent: false,
        body: null,
        replaceContent: true,
    };

    init() {
        this._loadContent();
    }

    _loadContent() {
        if (!this.options.url) {
            return;
        }

        const method = String(this.options.method || 'POST').toUpperCase();
        const useLoadingIndicator = !this.options.silent;

        let loadingIndicatorUtil = null;

        if (useLoadingIndicator) {
            loadingIndicatorUtil = new LoadingIndicatorUtil(this.el);
            loadingIndicatorUtil.create();
        }

        const fetchOptions = {
            method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        if (method === 'POST') {
            fetchOptions.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
            fetchOptions.body = this.options.body ?? '';
        }

        fetch(this.options.url, fetchOptions)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Request failed with status ${response.status}`);
                }

                return response.text();
            })
            .then((response) => {
                this._processResponse(response);
            })
            .catch((error) => {
                console.error('MoorlAjaxWidgetPlugin:', error);
            })
            .finally(() => {
                if (loadingIndicatorUtil) {
                    loadingIndicatorUtil.remove();
                }
            });
    }

    _processResponse(response) {
        if (this.options.replaceContent) {
            this.el.innerHTML = response;
        } else {
            this.el.insertAdjacentHTML('beforeend', response);
        }

        window.PluginManager.initializePluginsInParentElement(this.el);

        this.$emitter.publish('widgetContentLoaded', {
            el: this.el,
        });
    }
}
