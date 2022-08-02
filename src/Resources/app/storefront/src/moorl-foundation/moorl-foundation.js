import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import Feature from 'src/helper/feature.helper';

export default class MoorlFoundation extends Plugin {
    init() {
        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._registerModalEvents();
        this.callback = null;
    }

    _registerModalEvents() {
        const that = this;

        const buttons = document.querySelectorAll('[data-moorl-foundation-modal]');
        const modals = document.querySelectorAll('.modal');

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                let url = this.dataset.moorlFoundationModal;

                that._client.get(url, (response) => {
                    that._openModal(response, null);
                });
            });
        });

        if (Feature.isActive('v6.5.0.0')) {
            modals.forEach((modal) => {
                modal.addEventListener('hidden.bs.modal', () => {
                    const modalBody = modal.querySelectorAll('.moorl-foundation-modal-body');
                    modalBody[0].innerHTML = "";
                });
            });
        } else {
            jQuery('body').on('hidden.bs.modal', function () {
                jQuery('.moorl-foundation-modal-body video').trigger('pause');
                jQuery('.moorl-foundation-modal-body iframe').attr('src', null);
            });
        }

        window.moorlFoundationModal = function (url, callback) {
            that._client.get(url, (response) => {
                that._openModal(response, callback);
            });
        }
    }

    _openModal(response, callback) {
        if (Feature.isActive('v6.5.0.0')) {
            const modal = document.getElementById('moorlFoundationModal');
            modal.innerHTML = response;
            modal.show();
        } else {
            jQuery('#moorlFoundationModal').html(response).modal('show');
        }

        if (typeof callback == 'function') {
            callback();
        }
    }
}
