import ListHelper from './helper/list.helper';
import ItemHelper from './helper/item.helper';

const MoorlFoundation = {
    ListHelper,
    ItemHelper
};

MoorlFoundation.prototype = {};

window.MoorlFoundation = MoorlFoundation;
exports.default = MoorlFoundation;
module.exports = exports.default;
