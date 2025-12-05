const Plugin = window.PluginBaseClass;
import SignaturePad from 'signature_pad';

export default class MoorlFbSignaturePlugin extends Plugin {
    static options = {
        dotSize: 1.0,
        minWidth: 0.5,
        maxWidth: 2.5,
        throttle: 16,
        minDistance: 1,
        backgroundColor: "rgba(0, 0, 0, 0)",
        penColor: "black",
        velocityFilterWeight: 0.7,
        width: 500,
        height: 150,
    };

    init() {
        this._canvas = this.el.querySelector('canvas');
        this._canvas.width = this.options.width;
        this._canvas.height = this.options.height;

        this._input = this.el.querySelector('input');
        this._button = this.el.querySelector('[data-fb-refresh]');

        this.signaturePad = new SignaturePad(this._canvas, this.options);

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
