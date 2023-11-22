import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlSvgLoaderPlugin extends Plugin {
    static options = {
        src: null,
        attributes: null
    };

    init() {
        fetch(this.options.src)
            .then(body => body.text())
            .then(body => {
                this.el.innerHTML = body;
            });
    }
}
