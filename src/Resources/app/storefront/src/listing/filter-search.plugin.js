import FilterBasePlugin from 'src/plugin/listing/filter-base.plugin';
import DomAccess from 'src/helper/dom-access.helper';
import deepmerge from 'deepmerge';

export default class MoorlFoundationFilterSearchPlugin extends FilterBasePlugin {

    static options = deepmerge(FilterBasePlugin.options, {
        inputSearchSelector: '.search',
        inputInvalidCLass: 'is-invalid',
        inputTimeout: 1000,
        searchKey: 'search',
        containerSelector: '.filter-search-container'
    });

    init() {
        this._container = DomAccess.querySelector(this.el, this.options.containerSelector);
        this._inputSearch = DomAccess.querySelector(this.el, this.options.inputSearchSelector);
        this._timeout = null;
        this._hasError = false;

        this._registerEvents();
    }

    /**
     * @private
     */
    _registerEvents() {
        this._inputSearch.addEventListener('input', this._onChangeInput.bind(this));
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
            }
            this.listing.changeListing();
        }, this.options.inputTimeout);
    }

    /**
     * @return {Object}
     * @public
     */
    getValues() {
        const values = {};

        values[this.options.searchKey] = this._inputSearch.value;

        return values;
    }

    /**
     * @return {boolean}
     * @private
     */
    _isInputInvalid() {
        return this._inputSearch.value.length < 4;
    }

    /**
     * @return {string}
     * @private
     */
    _getErrorMessageTemplate() {
        return `<div class="${this.options.errorContainerClass}">${this.options.snippets.filterSearchErrorMessage}</div>`;
    }

    /**
     * @private
     */
    _setError() {
        if (this._hasError) {
            return;
        }

        this._inputSearch.classList.add(this.options.inputInvalidCLass);
        this._inputDistance.classList.add(this.options.inputInvalidCLass);

        this._container.insertAdjacentHTML('afterend', this._getErrorMessageTemplate());

        this._hasError = true;
    }

    /**
     * @private
     */
    _removeError() {
        this._inputSearch.classList.remove(this.options.inputInvalidCLass);

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
            if (key === this.options.searchKey) {
                this._inputSearch.value = params[key];
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

        if (this._inputSearch.value.length) {
            if (this._inputSearch.value.length) {
                labels.push({
                    label: `${this._inputSearch.value}`,
                    id: this.options.searchKey,
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
        if (id === this.options.searchKey) {
            this._inputSearch.value = '';
        }

        this._removeError();
    }

    /**
     * @public
     */
    resetAll() {
        this._inputSearch.value = '';
        this._removeError();
    }
}
