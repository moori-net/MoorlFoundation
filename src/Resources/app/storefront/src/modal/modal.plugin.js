import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlModal extends Plugin {
    init() {
        this._registerEvents();
    }

    _registerEvents() {
        this.el.addEventListener('click', () => {
            let url = this.el.dataset.moorlModal;

            window.moorlFoundationModal(url);
        });
    }
}
