import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlPaintPlugin extends Plugin {
    static options = {
        assetPath: '/bundles/moorlfoundation/storefront/js/paint/'
    };

    init() {
        if ('paintWorklet' in CSS) {
            this._addModules()
        }
    }

    _addModules() {
        CSS.paintWorklet.addModule(this.options.assetPath + 'checkerboard.js');
        CSS.paintWorklet.addModule(this.options.assetPath + 'dots.js');
        CSS.paintWorklet.addModule(this.options.assetPath + 'generated-dots.js');
        CSS.paintWorklet.addModule(this.options.assetPath + 'twinkle.js');
    }
}
