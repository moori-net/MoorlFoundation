const Plugin = window.PluginBaseClass;
import HttpClient from 'src/service/http-client.service';

export default class MoorlModal extends Plugin {
    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);

        this._registerEvents();
    }

    _registerEvents() {
        this.el.addEventListener('click', (evt) => {
            const url = this.el.dataset.moorlModal;
            if (!url) {return;}
            evt.preventDefault();
            this._openModal(url);
        });
    }

    _openModal(url, callback) {
        this._client.get(url, (response) => {
            const modal = document.getElementById('moorlFoundationModal');
            if (!modal) {
                console.error(`#moorlFoundationModal not found. Please be sure, that views/storefront/base.html.twig is extended correctly`)
            }

            modal.innerHTML = response;

            const bsModal =
                bootstrap.Modal.getInstance(modal) ?? new bootstrap.Modal(modal);
            bsModal.show();

            modal.addEventListener('hidden.bs.modal', () => {
                modal.innerHTML = '';
            });

            if (typeof callback == 'function') {
                callback(modal, bsModal);
            }
        });
    }
}
