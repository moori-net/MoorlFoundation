const Plugin = window.PluginBaseClass;

export default class MoorlModal extends Plugin {
    init() {
        this._registerEvents();
    }

    _registerEvents() {
        this.el.addEventListener('click', (evt) => {
            const url = this.el.dataset.moorlModal;
            if (!url) {return;}
            evt.preventDefault();
            window.moorlFoundationModal(url);
        });
    }
}
