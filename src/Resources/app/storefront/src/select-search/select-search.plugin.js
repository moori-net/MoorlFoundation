import Plugin from 'src/plugin-system/plugin.class';
import Choices from "choices.js";

export default class MoorlSelectSearchPlugin extends Plugin {
    static options = {
        desktop: true,
        mobile: false
    };

    init() {
        const sortingchoices = new Choices(this.el, {
            placeholder: false,
            itemSelectText: ''
        });
    }
}
