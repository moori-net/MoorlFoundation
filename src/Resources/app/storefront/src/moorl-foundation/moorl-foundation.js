const Plugin = window.PluginBaseClass;
import HttpClient from 'src/service/http-client.service';

export default class MoorlFoundation extends Plugin {
    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._registerModalEvents();
        this.callback = null;
    }

    _registerModalEvents() {
        const that = this;

        /* @deprecated: Use data-moorl-modal as future selector */
        const buttons = document.querySelectorAll('[data-moorl-foundation-modal]');

        if (buttons.length === 0) {
            return;
        }

        const modal = document.getElementById('moorlFoundationModal');
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                let url = button.dataset.moorlFoundationModal;

                this._client.get(url, (response) => {
                    this._openModal(response, null);
                });
            });
        });

        modal.addEventListener('hidden.bs.modal', () => {
            modal.innerHTML = '';
        });

        window.moorlFoundationModal = function (url, callback) {
            that._client.get(url, (response) => {
                that._openModal(response, callback);
            });
        };
    }

    _openModal(response, callback) {
        const modal = document.getElementById('moorlFoundationModal');
        modal.innerHTML = response;

        const bsModal =
            bootstrap.Modal.getInstance(modal) ?? new bootstrap.Modal(modal);
        bsModal.show();

        window.PluginManager.initializePlugins();

        if (typeof callback == 'function') {
            callback(modal, bsModal);
        }
    }
}
