import './core/moorl-foundation';
import './store/moorl-foundation.store';
import './init/foundation-api-service.init';

import './abstract';
import './proxy';

import './mixin/flow-action-helper.mixin';

import './component';
import './extension';
import './module';

import './main.scss';

const MoorlProxyService = Shopware.Service('moorlProxyService');

MoorlProxyService.registerPlugin({
    entity: 'product',
    listingRoute: 'sw.product.index'
});

MoorlProxyService.registerPlugin({
    entity: 'category',
    listingRoute: 'sw.category.index'
});

MoorlProxyService.registerPlugin({
    entity: 'customer',
    listingRoute: 'sw.customer.index'
});

MoorlProxyService.registerPlugin({
    entity: 'product_stream',
    listingRoute: 'sw.product.stream.index'
});

MoorlProxyService.registerPlugin({
    entity: 'cms_page',
    listingRoute: 'sw.cms.index'
});
