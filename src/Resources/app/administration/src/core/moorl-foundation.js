import fieldsetsConfig from './config/fieldsets.config';
import ListHelper from './helper/list.helper';
import ItemHelper from './helper/item.helper';
import TranslationHelper from './helper/translation.helper';
import RouteHelper from './helper/route.helper';
import FormBuilderHelper from './helper/form-builder.helper';
import ConditionHelper from './helper/condition.helper';
import CmsElementHelper from './helper/cms-element.helper';
import ModuleHelper from './helper/module.helper';
import MappingHelper from './helper/mapping.helper';

const MoorlFoundation = {
    fieldsetsConfig,
    ListHelper,
    ItemHelper,
    TranslationHelper,
    RouteHelper,
    FormBuilderHelper,
    ConditionHelper,
    CmsElementHelper,
    ModuleHelper,
    MappingHelper
};

window.MoorlFoundation = MoorlFoundation;

exports.default = MoorlFoundation;

module.exports = exports.default;

window.dispatchEvent(new Event('MoorlFoundationReady'));
