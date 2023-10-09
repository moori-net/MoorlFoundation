import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlCharCounterPlugin extends Plugin {
    static options = {
        inputElId: null,
        type: null
    };

    init() {
        if (!this.options.inputElId) {
            return;
        }

        this._inputEl = document.getElementById(this.options.inputElId);
        if (!this._inputEl) {
            return;
        }

        this._maxLength = parseInt(this._inputEl.maxLength);
        this._minLength = parseInt(this._inputEl.minLength);
        if (!this._maxLength) {
            return;
        }

        if (this.options.type === 'progress-bar') {
            this._progressBarEl = this.el.querySelector('.progress-bar');
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
        this._currentLength = parseInt(this._inputEl.value.length);

        if (this.options.type === 'progress-bar') {
            this._currentPercentage = Math.ceil(this._currentLength / this._maxLength * 100);

            if (this._currentPercentage >= 100) {
                this._progressBarEl.classList.remove('bg-success');
                this._progressBarEl.classList.remove('bg-warning');
                this._progressBarEl.classList.add('bg-danger');
            } else if (this._currentPercentage >= 90) {
                this._progressBarEl.classList.remove('bg-success');
                this._progressBarEl.classList.remove('bg-danger');
                this._progressBarEl.classList.add('bg-warning');
            } else {
                this._progressBarEl.classList.remove('bg-danger');
                this._progressBarEl.classList.remove('bg-warning');
                this._progressBarEl.classList.add('bg-success');
            }

            this._progressBarEl.style.width = `${this._currentPercentage}%`;
            this._progressBarEl.innerText = `${this._currentLength}/${this._maxLength}`;
        } else {
            this.el.innerText = `${this._currentLength}/${this._maxLength}`;
        }
    }
}
