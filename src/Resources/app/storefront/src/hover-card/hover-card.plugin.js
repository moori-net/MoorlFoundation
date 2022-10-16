import Plugin from 'src/plugin-system/plugin.class';

export default class MoorlHoverCardPlugin extends Plugin {
    static options = {};

    init() {
        this._registerEvents();
    }

    _registerEvents() {
    }
}
