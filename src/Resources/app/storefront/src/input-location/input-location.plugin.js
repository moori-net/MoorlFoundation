const Plugin = window.PluginBaseClass;


export default class MoorlInputLocationPlugin extends Plugin {
    static options = {
        inputLocationSelector: '.location',
        buttonMyLocationSelector: '.my-location',
    };

    init() {
        this._inputLocation = this.el.querySelector(this.options.inputLocationSelector);
        this._buttonMyLocation = this.el.querySelector(this.options.buttonMyLocationSelector);

        this._registerEvents();
    }

    _registerEvents() {
        if (this._buttonMyLocation) {
            this._buttonMyLocation.addEventListener(
                'click',
                this._onClickButton.bind(this)
            );
        }
    }

    _onClickButton() {
        this._inputLocation.value = `0|0`;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                this._inputLocation.value = `${position.coords.latitude}|${position.coords.longitude}`;
                this._inputLocation.dispatchEvent(new Event('input'));
            });
        } else {
        }
    }
}
