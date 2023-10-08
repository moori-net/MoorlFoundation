import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlCharCounterPlugin extends Plugin {
    static options = {
        inputElId: null
    };

    init() {
        if (!this.options.inputElId) {
            return;
        }

        this._inputEl = document.getElementById(this.options.inputElId);
        if (!this._inputEl) {
            return;
        }

        this._maxLength = this._inputEl.maxLength;
        this._minLength = this._inputEl.minLength;
        if (!this._maxLength) {
            return;
        }

        this._writeCurrent();

        setTimeout(() => {
            this._writeCurrent();
        }, 1000);

        this._registerEvents();
    }

    _registerEvents() {
        ['oninput', 'keyup', 'change'].forEach(evt => {
            this._inputEl.addEventListener(evt, () => {
                this._writeCurrent();
            });
        });
    }

    _writeCurrent() {
        this._currentLength = this._inputEl.value.length;
        this.el.innerText = `${this._currentLength}/${this._maxLength}`;
    }
}
