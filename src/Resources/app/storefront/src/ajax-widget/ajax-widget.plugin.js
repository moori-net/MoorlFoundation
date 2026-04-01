import Plugin from 'src/plugin-system/plugin.class';
import LoadingIndicatorUtil from 'src/utility/loading-indicator/loading-indicator.util';

export default class MoorlAjaxWidgetPlugin extends Plugin {
    static options = {
        url: null
    };

    init() {
        this._loadContent();
    }

    _loadContent() {
        if (!this.options.url) {
            return;
        }

        const loadingIndicatorUtil = new LoadingIndicatorUtil(this.el);
        loadingIndicatorUtil.create();

        fetch(this.options.url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
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
                loadingIndicatorUtil.remove();
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
