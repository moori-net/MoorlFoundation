import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class MoorlProductBuyListPlugin extends Plugin {
    static options = {
        locale: document.documentElement.lang,
        currencyIso: "EUR",
        actionUrl: null
    };

    init() {
        this._priceElements = this.el.querySelectorAll('[data-price]');
        this._totalPriceElement = this.el.querySelector('.total-price');
        this._selectedItemsElement = this.el.querySelector('.selected-items');
        this._formValuesElement = this.el.querySelector('.form-values');

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
            })
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
