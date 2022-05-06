import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlPaintPlugin extends Plugin {
    static options = {
        assetPath: '/bundles/moorlfoundation/storefront/js/paint/'
    };

    init() {
        if ('paintWorklet' in CSS) {
            CSS.paintWorklet.addModule(`${this.options.assetPath}dots.js`);
            CSS.paintWorklet.addModule(`${this.options.assetPath}generateddots.js`);
        }

        this.el.addEventListener("mousemove", e => {
            let bounds = this.el.getBoundingClientRect();

            this.el.style.setProperty('--mouse-x', e.clientX - bounds.left);
            this.el.style.setProperty('--mouse-y', e.clientY - bounds.top);
        });
    }
}
