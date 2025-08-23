const Plugin = window.PluginBaseClass;

export default class MoorlSvgLoaderPlugin extends Plugin {
    static options = {
        src: null,
        attributes: null,
    };

    init() {
        fetch(this.options.src)
            .then((body) => body.text())
            .then((body) => {
                this.el.innerHTML = body;
            });
    }
}
