const Plugin = window.PluginBaseClass;

export default class MoorlCopyPlugin extends Plugin {
    static options = {};

    init() {
        this._registerEvents();
    }

    _registerEvents() {
        this.el.addEventListener('click', () => {
            try {
                navigator.clipboard.writeText(this.el.innerText).then(() => {
                    this.el.classList.add('success');
                    setTimeout(() => {
                        this.el.classList.remove('success');
                    }, 1000);
                });
            } catch (err) {
                console.error('Failed to copy: ', err);
            }
        });
    }
}
