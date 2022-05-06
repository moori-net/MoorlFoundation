import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlPaintPlugin extends Plugin {
    static options = {
        assetPath: '/bundles/moorlfoundation/storefront/js/paint/'
    };

    init() {
        if ('paintWorklet' in CSS) {
            let path = `${this.options.assetPath}${this.options.module}.js`;
            CSS.paintWorklet.addModule(path);
        }

        this.el.addEventListener("mousemove", e => {
            let bounds = this.el.getBoundingClientRect();

            this.el.style.setProperty('--mouse-x', e.clientX - bounds.left);
            this.el.style.setProperty('--mouse-y', e.clientY - bounds.top);
        });
    }
}
