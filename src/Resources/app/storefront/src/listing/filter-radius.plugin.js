import FilterBasePlugin from 'src/plugin/listing/filter-base.plugin';
import DomAccess from 'src/helper/dom-access.helper';
import deepmerge from 'deepmerge';

export default class MoorlFoundationFilterRadiusPlugin extends FilterBasePlugin {

    static options = deepmerge(FilterBasePlugin.options, {
        inputLocationSelector: '.location',
        buttonMyLocationSelector: '.my-location',
        inputDistanceSelector: '.distance',
        inputInvalidCLass: 'is-invalid',
        inputTimeout: 1000,
        locationKey: 'location',
        distanceKey: 'distance',
        errorContainerClass: 'filter-radius-error',
        containerSelector: '.filter-radius-container',
        snippets: {
            filterRadiusActiveLocationLabel: '',
            filterRadiusActiveDistanceLabel: '',
            filterRadiusErrorMessage: '',
        },
    });

    init() {
        this._container = DomAccess.querySelector(this.el, this.options.containerSelector);
        this._inputLocation = DomAccess.querySelector(this.el, this.options.inputLocationSelector);
        this._inputDistance = DomAccess.querySelector(this.el, this.options.inputDistanceSelector);
        this._buttonMyLocation = this.el.querySelector(this.options.buttonMyLocationSelector);
        this._timeout = null;
        this._hasError = false;

        this._registerEvents();
    }

    /**
     * @private
     */
    _registerEvents() {
        this._inputLocation.addEventListener('input', this._onChangeInput.bind(this));
        this._inputDistance.addEventListener('input', this._onChangeInput.bind(this));

        if (this._buttonMyLocation) {
            this._buttonMyLocation.addEventListener('click', this._onClickButton.bind(this));
        }
    }

    /**
     * @private
     */
    _onChangeInput() {
        clearTimeout(this._timeout);

        this._timeout = setTimeout(() => {
            if (this._isInputInvalid()) {
                this._setError();
            } else {
                this._removeError();
                this.listing.changeListing();
            }
        }, this.options.inputTimeout);
    }

    /**
     * @private
     */
    _onClickButton() {
        console.log("Request geolocation");
        this._inputLocation.value = `0|0`;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition( (position) => {
                this._inputLocation.value = `${position.coords.latitude}|${position.coords.longitude}`;
                this._onChangeInput();
            });
        } else {
            console.log("Geolocation is not supported by this browser");
        }
    }

    /**
     * @return {Object}
     * @public
     */
    getValues() {
        const values = {};

        values[this.options.locationKey] = this._inputLocation.value;
        values[this.options.distanceKey] = this._inputDistance.value;

        return values;
    }

    /**
     * @return {boolean}
     * @private
     */
    _isInputInvalid() {
        let cond1 = this._inputLocation.value.length < 3;
        let cond2 = this._inputDistance.value.length === 0;

        return cond1 || cond2;
    }

    /**
     * @return {string}
     * @private
     */
    _getErrorMessageTemplate() {
        return `<div class="${this.options.errorContainerClass}">${this.options.snippets.filterRadiusErrorMessage}</div>`;
    }

    /**
     * @private
     */
    _setError() {
        if (this._hasError) {
            return;
        }

        this._inputLocation.classList.add(this.options.inputInvalidCLass);
        this._inputDistance.classList.add(this.options.inputInvalidCLass);

        this._container.insertAdjacentHTML('afterend', this._getErrorMessageTemplate());

        this._hasError = true;
    }

    /**
     * @private
     */
    _removeError() {
        this._inputLocation.classList.remove(this.options.inputInvalidCLass);
        this._inputDistance.classList.remove(this.options.inputInvalidCLass);

        const error = DomAccess.querySelector(this.el, `.${this.options.errorContainerClass}`, false);

        if (error) {
            error.remove();
        }

        this._hasError = false;
    }

    /**
     * @param params
     * @public
     * @return {boolean}
     */
    setValuesFromUrl(params) {
        let stateChanged = false;
        Object.keys(params).forEach(key => {
            if (key === this.options.locationKey) {
                this._inputLocation.value = params[key];
                stateChanged = true;
            }
            if (key === this.options.distanceKey) {
                this._inputDistance.value = params[key];
                stateChanged = true;
            }
        });

        return stateChanged;
    }

    /**
     * @return {Array}
     * @public
     */
    getLabels() {
        let labels = [];

        if (this._inputLocation.value.length && this._inputDistance.value.length) {
            if (this._inputLocation.value.length) {
                labels.push({
                    label: `${this._inputDistance.value}${this.options.snippets.filterRadiusActiveDistanceLabel} ${this.options.snippets.filterRadiusActiveLocationLabel} "${this._inputLocation.value}"`,
                    id: this.options.locationKey,
                });
            }
        } else {
            labels = [];
        }

        return labels;
    }

    /**
     * @param id
     * @public
     */
    reset(id) {
        if (id === this.options.locationKey) {
            this._inputLocation.value = '';
        }

        if (id === this.options.distanceKey) {
            this._inputDistance.value = '';
        }

        this._removeError();
    }

    /**
     * @public
     */
    resetAll() {
        this._inputLocation.value = '';
        this._inputDistance.value = '';
        this._removeError();
    }
}
