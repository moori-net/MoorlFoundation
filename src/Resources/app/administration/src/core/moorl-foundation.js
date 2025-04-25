import Logger from './logger';
import ListHelper from './helper/list.helper';
import ItemHelper from './helper/item.helper';
import TranslationHelper from './helper/translation.helper';
import RouteHelper from './helper/route.helper';
import FormBuilderHelper from './helper/form-builder.helper';
import ConditionHelper from './helper/condition.helper';
import CmsElementHelper from './helper/cms-element.helper';

const MoorlFoundation = {
    Logger,
    ListHelper,
    ItemHelper,
    TranslationHelper,
    RouteHelper,
    FormBuilderHelper,
    ConditionHelper,
    CmsElementHelper
};

MoorlFoundation.prototype = {};

window.MoorlFoundation = MoorlFoundation;
exports.default = MoorlFoundation;
module.exports = exports.default;
