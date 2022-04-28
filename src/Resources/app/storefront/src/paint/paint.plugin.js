import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlPaintPlugin extends Plugin {
    static options = {
        assetPath: '/bundles/moorlfoundation/storefront/js/paint/',
        module: 'dots'
    };

    init() {
        if ('paintWorklet' in CSS) {
            let path = `${this.options.assetPath}${this.options.module}.js`;
            CSS.paintWorklet.addModule(path);
        }

        this.el.addEventListener("mousemove", e => {
            this.el.style.setProperty('--mouse-x', e.clientX + "px");
            this.el.style.setProperty('--mouse-y', e.clientY + "px");
        });

        this.el.style.backgroundImage = `paint(${this.options.module})`;
    }
}
