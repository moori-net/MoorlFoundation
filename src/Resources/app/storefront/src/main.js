/* Old plugins */
import MoorlFoundation from './moorl-foundation/moorl-foundation';
import MoorlFoundationForm from './moorl-foundation-form/moorl-foundation-form';
import MoorlAnimation from './moorl-animation/moorl-animation';
/* New plugins */
import MoorlRelativeTimePlugin from './relative-time/relative-time.plugin';
import MoorlCountdownPlugin from './countdown/countdown.plugin';
import MoorlProductBuyListPlugin from './product-buy-list/product-buy-list.plugin';
import MoorlPartsListPlugin from './parts-list/parts-list.plugin';
import MoorlTocPlugin from './toc/toc.plugin';
import MoorlGridPlugin from './grid/grid.plugin';
import MoorlPaintPlugin from './paint/paint.plugin';
import MoorlFoundationFilterRadiusPlugin from './listing/filter-radius.plugin';
import MoorlFoundationFilterSearchPlugin from './listing/filter-search.plugin';
import MoorlCustomerUploadPlugin from './customer-upload/customer-upload.plugin';
import MoorlLocationPlugin from './location/location.plugin';
import MoorlHoverCardPlugin from './hover-card/hover-card.plugin';
import MoorlCookieBoxPlugin from './cookie-box/cookie-box.plugin';
import MoorlModalPlugin from './modal/modal.plugin';
import MoorlCharCounterPlugin from './char-counter/char-counter.plugin';
import MoorlCopyPlugin from './copy/copy.plugin';
import MoorlSelectSearchPlugin from './select-search/select-search.plugin';
import MoorlSvgLoaderPlugin from './svg-loader/svg-loader.plugin';
import MoorlInputLocationPlugin from './input-location/input-location.plugin';
/* Plugins moved since Shopware 6.6 */
import MoorlFbSignaturePlugin from './fb-signature/fb-signature.plugin';

const PluginManager = window.PluginManager;
/* Old plugins */
PluginManager.register('MoorlFoundation', MoorlFoundation);
PluginManager.register(
    'MoorlFoundationForm',
    MoorlFoundationForm,
    '[data-moorl-foundation-form]'
);
PluginManager.register(
    'MoorlAnimation',
    MoorlAnimation,
    '[data-moorl-animation]'
);
/* New plugins */
PluginManager.register(
    'MoorlLocation',
    MoorlLocationPlugin,
    '[data-moorl-location]'
);
PluginManager.register(
    'MoorlRelativeTime',
    MoorlRelativeTimePlugin,
    '[data-moorl-relative-time]'
);
PluginManager.register(
    'MoorlCountdown',
    MoorlCountdownPlugin,
    '[data-moorl-countdown]'
);
PluginManager.register(
    'MoorlProductBuyList',
    MoorlProductBuyListPlugin,
    '[data-moorl-product-buy-list]'
);
PluginManager.register(
    'MoorlPartsList',
    MoorlPartsListPlugin,
    '[data-moorl-parts-list]'
);
PluginManager.register('MoorlToc', MoorlTocPlugin, '[data-moorl-toc]');
PluginManager.register('MoorlGrid', MoorlGridPlugin, '[data-moorl-grid]');
PluginManager.register('MoorlPaint', MoorlPaintPlugin, '.moorl-paint');
PluginManager.register(
    'MoorlFoundationFilterRadius',
    MoorlFoundationFilterRadiusPlugin,
    '[data-moorl-foundation-filter-radius]'
);
PluginManager.register(
    'MoorlFoundationFilterSearch',
    MoorlFoundationFilterSearchPlugin,
    '[data-moorl-foundation-filter-search]'
);
PluginManager.register(
    'MoorlCustomerUpload',
    MoorlCustomerUploadPlugin,
    '[data-moorl-customer-upload]'
);
PluginManager.register(
    'MoorlHoverCard',
    MoorlHoverCardPlugin,
    '[data-moorl-hover-card]'
);
PluginManager.register(
    'MoorlCookieBox',
    MoorlCookieBoxPlugin,
    '[data-moorl-cookie-box]'
);
PluginManager.register('MoorlModal', MoorlModalPlugin, '[data-moorl-modal]');
PluginManager.register(
    'MoorlCharCounter',
    MoorlCharCounterPlugin,
    '[data-moorl-char-counter]'
);
PluginManager.register('MoorlCopy', MoorlCopyPlugin, '[data-moorl-copy]');
PluginManager.register(
    'MoorlSelectSearch',
    MoorlSelectSearchPlugin,
    '[data-moorl-select-search]'
);
PluginManager.register(
    'MoorlSvgLoader',
    MoorlSvgLoaderPlugin,
    '[data-moorl-svg-loader]'
);
PluginManager.register(
    'MoorlInputLocation',
    MoorlInputLocationPlugin,
    '[data-moorl-input-location]'
);
/* Plugins moved since Shopware 6.6 */
PluginManager.register(
    'MoorlFbSignature',
    MoorlFbSignaturePlugin,
    '[data-moorl-fb-signature]'
);

if (window.moorlAnimation) {
    for (let item of window.moorlAnimation) {
        PluginManager.register(
            'MoorlAnimation',
            MoorlAnimation,
            item.cssSelector,
            item
        );
    }
}

if (module.hot) {
    module.hot.accept();
}
