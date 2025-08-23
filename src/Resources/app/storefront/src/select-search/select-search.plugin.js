const Plugin = window.PluginBaseClass;
import TomSelect from 'tom-select';

export default class MoorlSelectSearchPlugin extends Plugin {
    static options = {
        desktop: true,
        mobile: false,
    };

    init() {
        const selectSearch = new TomSelect(this.el, {});
    }
}
