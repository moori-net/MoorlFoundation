import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlPaintPlugin extends Plugin {
    static options = {
        assetPath: '/bundles/moorlfoundation/storefront/js/paint/'
    };

    init() {
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
