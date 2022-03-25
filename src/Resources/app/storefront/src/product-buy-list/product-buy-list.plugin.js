import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import FormSerializeUtil from 'src/utility/form/form-serialize.util';
import queryString from 'query-string';

export default class MoorlProductBuyListPlugin extends Plugin {
    static options = {
        locale: document.documentElement.lang,
        currencyIso: "EUR",
        enablePrices: true,
        enableAddToCart: true
    };

    init() {
        this._priceElements = this.el.querySelectorAll('[data-price]');
        this._productListItems = this.el.querySelectorAll('[data-moorl-product-buy-list-item]');
        this._totalPriceElement = this.el.querySelector('.total-price');
        this._selectedItemsElement = this.el.querySelector('.selected-items');
        this._formValuesElement = this.el.querySelector('.form-values');

        this._client = new HttpClient(window.accessKey, window.contextToken);

        this._updateTotalPrice();
        this._registerEvents();
    }

    _registerEvents() {
        const that = this;

        document.addEventListener('change', event => {
            that._priceElements.forEach(item => {
                if (event.target === item) {
                    that._updateTotalPrice();
                }
            });

            if (event.target.nodeName === 'SELECT') {
                const item = event.target.closest('[data-moorl-product-buy-list-item]');
                const form = event.target.form;
                const actionUrl = form.action;
                const formData = new FormData(form);
                const object = {};
                formData.forEach(function (value, key) {
                    object[key] = value;
                });
                const query = {
                    switched: event.target.name,
                    options: JSON.stringify(object),
                    enablePrices: that.options.enablePrices,
                    enableAddToCart: that.options.enableAddToCart
                };

                that._client.get(actionUrl + "?" + queryString.stringify(query), (response) => {
                    console.log(response);
                    item.innerHTML = response;
                    that._updateTotalPrice();
                });
            }
        });
    }

    _updateTotalPrice() {
        const that = this;
        const currency = new Intl.NumberFormat(this.options.locale, {
            style: 'currency',
            currency: this.options.currencyIso,
        });

        let totalPrice = 0;
        let selectedItems = 0;

        this._formValuesElement.innerHTML = null;

        this._priceElements.forEach(item => {
            if (!item.checked) {
                return;
            }
            totalPrice = totalPrice + parseFloat(item.dataset.price);
            selectedItems++;
            that._createFormValues(item.value);
        })

        this._totalPriceElement.innerText = currency.format(totalPrice);
        this._selectedItemsElement.innerText = selectedItems;
    }

    _createFormValues(productId) {
        this._formValuesElement.appendChild(this._createFormValue(`lineItems[${productId}][id]`, productId));
        this._formValuesElement.appendChild(this._createFormValue(`lineItems[${productId}][type]`, 'product'));
        this._formValuesElement.appendChild(this._createFormValue(`lineItems[${productId}][referencedId]`, productId));
        this._formValuesElement.appendChild(this._createFormValue(`lineItems[${productId}][stackable]`, 1));
        this._formValuesElement.appendChild(this._createFormValue(`lineItems[${productId}][removable]`, 1));
        this._formValuesElement.appendChild(this._createFormValue(`lineItems[${productId}][quantity]`, 1));
    }

    _createFormValue(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.value = value;
        input.name = name;
        return input;
    }
}
