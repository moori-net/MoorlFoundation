import Plugin from 'src/plugin-system/plugin.class';
import SignaturePad from "signature_pad";

export default class MoorlFbSignaturePlugin extends Plugin {
    static options = {
        dotSize: null,
        minWidth: null,
        maxWidth: null,
        throttle: null,
        minDistance: null,
        backgroundColor: null,
        penColor: null,
        velocityFilterWeight: 0.7
    };

    init() {
        this._canvas = this.el.querySelector('canvas');
        this._input = this.el.querySelector('input');
        this._button = this.el.querySelector('[data-fb-refresh]');

        this.signaturePad = new SignaturePad(this._canvas);

        this.signaturePad.addEventListener("endStroke", () => {
            this._input.value = this.signaturePad.toDataURL();
        });

        this._button.addEventListener('click', () => {
            this.signaturePad.clear();
            this._input.value = "";
        });

        if (this._input.value) {
            this.signaturePad.fromDataURL(this._input.value);
        }
    }
}
