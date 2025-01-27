import FilterBasePlugin from 'src/plugin/listing/filter-base.plugin';
import DomAccess from 'src/helper/dom-access.helper';
import deepmerge from 'deepmerge';
import CookieStorageHelper from 'src/helper/storage/cookie-storage.helper';

export default class MoorlFoundationFilterRadiusPlugin extends FilterBasePlugin {

    static options = deepmerge(FilterBasePlugin.options, {
        inputLocationSelector: '.location',
        inputDistanceSelector: '.distance',
        inputPersistSelector: '.radius-persist',
        inputInvalidCLass: 'is-invalid',
        inputTimeout: 1000,
        locationKey: 'location',
        distanceKey: 'distance',
        persistKey: 'radius-persist',
        errorContainerClass: 'filter-radius-error',
        containerSelector: '.filter-radius-container',
        defaultValue: null,
        filterRadiusPersist: false,
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
        if (this.options.filterRadiusPersist) {
            this._inputPersist = DomAccess.querySelector(this.el, this.options.inputPersistSelector);
        }
        this._timeout = null;
        this._hasError = false;

        if (this.options.defaultValue) {
            this._inputDistance.value = this.options.defaultValue;
        }

        this._registerEvents();
        this._setValuesFromCookie();
    }

    /**
     * @private
     */
    _registerEvents() {
        this._inputLocation.addEventListener('input', this._onChangeInput.bind(this));
        this._inputDistance.addEventListener('input', this._onChangeInput.bind(this));
        if (this.options.filterRadiusPersist) {
            this._inputPersist.addEventListener('change', this._onChangeInput.bind(this));
        }
    }

    /**
     * @private
     */
    _onChangeInput() {
        console.log("_onChangeInput");

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
     * @return {Object}
     * @public
     */
    getValues() {
        const values = {};

        values[this.options.locationKey] = this._inputLocation.value;
        values[this.options.distanceKey] = this._inputDistance.value;
        if (this.options.filterRadiusPersist) {
            values[this.options.persistKey] = !!this._inputPersist.checked;
        }

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

    _setValuesFromCookie() {
        try {
            let radiusPersistCookieData = CookieStorageHelper.getItem(this.options.persistKey);
            if (this.options.filterRadiusPersist && radiusPersistCookieData) {
                let radiusPersistCookie = JSON.parse(decodeURIComponent(radiusPersistCookieData));

                this._inputLocation.value = radiusPersistCookie[this.options.locationKey];
                this._inputDistance.value = radiusPersistCookie[this.options.distanceKey];
                this._inputPersist.checked = true;

                this._onChangeInput();
            }
        } catch (e) {
            console.log(e);
        }
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
            if (this.options.filterRadiusPersist && key === this.options.persistKey) {
                this._inputPersist.checked = params[key];
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
        if (this.options.filterRadiusPersist && id === this.options.persistKey) {
            this._inputPersist.checked = false;
        }
        this._removeError();
    }

    /**
     * @public
     */
    resetAll() {
        this._inputLocation.value = '';
        this._inputDistance.value = '';
        if (this.options.filterRadiusPersist) {
           this._inputPersist.checked = false;
        }
        this._removeError();
    }
}
