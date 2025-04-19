import ListHelper from './helper/list.helper';
import ItemHelper from './helper/item.helper';
import TranslationHelper from './helper/translation.helper';

const MoorlFoundation = {
    ListHelper,
    ItemHelper,
    TranslationHelper
};

MoorlFoundation.prototype = {};

window.MoorlFoundation = MoorlFoundation;
exports.default = MoorlFoundation;
module.exports = exports.default;
