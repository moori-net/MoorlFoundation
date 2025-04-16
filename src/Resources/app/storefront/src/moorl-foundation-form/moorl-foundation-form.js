import Plugin from 'src/plugin-system/plugin.class';
import FormSerializeUtil from 'src/utility/form/form-serialize.util';
import DomAccess from 'src/helper/dom-access.helper';
import HttpClient from 'src/service/http-client.service';

export default class MoorlFoundationForm extends Plugin {
    static options = {};

    init() {
        this._form = this.el;

        if (!this._form) {
            return;
        }

        this._client = new HttpClient(window.accessKey, window.contextToken);
        this._reponse = null;

        this.el.addEventListener('submit', this._formSubmit.bind(this));
    }

    _formSubmit(event) {
        if (typeof event != 'undefined') {
            event.preventDefault();
        }

        const requestUrl = DomAccess.getAttribute(
            this._form,
            'action'
        ).toLowerCase();
        const formData = FormSerializeUtil.serialize(this._form);

        this._client.post(requestUrl, formData, this._onLoaded.bind(this));
    }

    _onLoaded(response) {
        this._reponse = JSON.parse(response);

        if (this._reponse.reload) {
            location.reload();
        }
    }
}
