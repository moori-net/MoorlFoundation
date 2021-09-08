import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class MoorlFoundation extends Plugin {
    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._registerModalEvents();
        this.callback = null;
    }

    _registerModalEvents() {
        const that = this;

        jQuery('body').on('click', '[data-moorl-foundation-modal]', function () {
            let url = this.dataset.moorlFoundationModal;

            that._client.get(url, (response) => {
                that._openModal(response, null);
            });
        });

        window.moorlFoundationModal = function (url, callback) {
            that._client.get(url, (response) => {
                that._openModal(response, callback);
            });
        }

        jQuery('body').on('hidden.bs.modal', function () {
            jQuery('.moorl-foundation-modal-body video').trigger('pause');
            jQuery('.moorl-foundation-modal-body iframe').attr('src', null);
        });
    }

    _openModal(response, callback) {
        jQuery('#moorlFoundationModal').html(response).modal('show');

        if (typeof callback == 'function') {
            callback();
        }
    }
}
