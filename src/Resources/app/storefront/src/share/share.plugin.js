const Plugin = window.PluginBaseClass;

export default class MoorlSharePlugin extends Plugin {
    static options = {
        title: null,
        url: null,
        text: null,
    };

    init() {
        if (typeof navigator.share !== 'function') {
            this.el.classList.add('d-none');
            return;
        }

        this.el.classList.remove('d-none');
        this._registerEvents();
    }

    _registerEvents() {
        this.el.addEventListener('click', async (event) => {
            event.preventDefault();

            const data = {
                title: this.options.title || document.title,
                url: this.options.url || window.location.href,
            };

            if (this.options.text) {
                data.text = this.options.text;
            }

            try {
                await navigator.share(data);
            } catch {
                // Dismissing or rejecting the native share dialog needs no UI feedback.
            }
        });
    }
}
