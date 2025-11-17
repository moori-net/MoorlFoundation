const PluginManager = window.PluginManager;

/* Old plugins */
PluginManager.register(
    'MoorlFoundation',
    () => import('./moorl-foundation/moorl-foundation'),
);

PluginManager.register(
    'MoorlFoundationForm',
    () => import('./moorl-foundation-form/moorl-foundation-form'),
    '[data-moorl-foundation-form]'
);

PluginManager.register(
    'MoorlAnimation',
    () => import('./moorl-animation/moorl-animation'),
    '[data-moorl-animation]'
);

/* New plugins */
PluginManager.register(
    'MoorlLocation',
    () => import('./location/location.plugin'),
    '[data-moorl-location]'
);

PluginManager.register(
    'MoorlRelativeTime',
    () => import('./relative-time/relative-time.plugin'),
    '[data-moorl-relative-time]'
);

PluginManager.register(
    'MoorlCountdown',
    () => import('./countdown/countdown.plugin'),
    '[data-moorl-countdown]'
);

PluginManager.register(
    'MoorlProductBuyList',
    () => import('./product-buy-list/product-buy-list.plugin'),
    '[data-moorl-product-buy-list]'
);

PluginManager.register(
    'MoorlPartsList',
    () => import('./parts-list/parts-list.plugin'),
    '[data-moorl-parts-list]'
);

PluginManager.register(
    'MoorlToc',
    () => import('./toc/toc.plugin'),
    '[data-moorl-toc]'
);

PluginManager.register(
    'MoorlGrid',
    () => import('./grid/grid.plugin'),
    '[data-moorl-grid]'
);

PluginManager.register(
    'MoorlPaint',
    () => import('./paint/paint.plugin'),
    '.moorl-paint'
);

PluginManager.register(
    'MoorlFoundationFilterRadius',
    () => import('./listing/filter-radius.plugin'),
    '[data-moorl-foundation-filter-radius]'
);

PluginManager.register(
    'MoorlFoundationFilterSearch',
    () => import('./listing/filter-search.plugin'),
    '[data-moorl-foundation-filter-search]'
);

PluginManager.register(
    'MoorlCustomerUpload',
    () => import('./customer-upload/customer-upload.plugin'),
    '[data-moorl-customer-upload]'
);

PluginManager.register(
    'MoorlHoverCard',
    () => import('./hover-card/hover-card.plugin'),
    '[data-moorl-hover-card]'
);

PluginManager.register(
    'MoorlCookieBox',
    () => import('./cookie-box/cookie-box.plugin'),
    '[data-moorl-cookie-box]'
);

PluginManager.register(
    'MoorlModal',
    () => import('./modal/modal.plugin'),
    '[data-moorl-modal]'
);

PluginManager.register(
    'MoorlCharCounter',
    () => import('./char-counter/char-counter.plugin'),
    '[data-moorl-char-counter]'
);

PluginManager.register(
    'MoorlCopy',
    () => import('./copy/copy.plugin'),
    '[data-moorl-copy]'
);

PluginManager.register(
    'MoorlSelectSearch',
    () => import('./select-search/select-search.plugin'),
    '[data-moorl-select-search]'
);

PluginManager.register(
    'MoorlSvgLoader',
    () => import('./svg-loader/svg-loader.plugin'),
    '[data-moorl-svg-loader]'
);

PluginManager.register(
    'MoorlInputLocation',
    () => import('./input-location/input-location.plugin'),
    '[data-moorl-input-location]'
);

/* Plugins moved since Shopware 6.6 */
PluginManager.register(
    'MoorlFbSignature',
    () => import('./fb-signature/fb-signature.plugin'),
    '[data-moorl-fb-signature]'
);

/* Dynamic MoorlAnimation configuration */
if (window.moorlAnimation) {
    for (let item of window.moorlAnimation) {
        PluginManager.register(
            'MoorlAnimation',
            () => import('./moorl-animation/moorl-animation'),
            item.cssSelector,
            item
        );
    }
}

if (module.hot) {
    module.hot.accept();
}
