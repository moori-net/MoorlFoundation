import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlPaintPlugin extends Plugin {
    static options = {
        assetPath: '/bundles/moorlfoundation/storefront/js/paint/'
    };

    init() {
        let root = document.documentElement;

        root.addEventListener("mousemove", e => {
            root.style.setProperty('--mouse-x', e.clientX + "px");
            root.style.setProperty('--mouse-y', e.clientY + "px");
        });

        if ('paintWorklet' in CSS) {
            const modules = [
                'checkerboard',
                'dots',
                'generated-dots',
                'twinkle',
            ];

            for (const module of modules) {
                let path = `${this.options.assetPath}${module}.js`;
                console.log(path);
                console.log(CSS);
                console.log(CSS.PaintWorklet);
                CSS.paintWorklet.addModule(path);
            }
        }
    }
}
