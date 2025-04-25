import './core/moorl-foundation';
import './store/moorl-foundation.store';
import './init/foundation-api-service.init';

import './mixin/flow-action-helper.mixin';
import './mixin/abstract-cms-element.mixin';

import './abstract';
import './proxy';

import './component';
import './extension';
import './module';

import './main.scss';

const MoorlProxyService = Shopware.Service('moorlProxyService');

MoorlProxyService.registerPlugin({
    entity: 'moorl_client',
    listingRoute: 'moorl.client.list',
    properties: [
        {name: 'active', visibility: 100},
        {name: 'name', visibility: 100},
        {name: 'type', visibility: 100},
    ]
});

MoorlProxyService.registerPlugin({
    entity: 'moorl_cms_element_config',
    listingRoute: 'moorl.cms.element.config.index',
    properties: [
        {name: 'name', visibility: 100},
        {name: 'type', visibility: 100},
    ]
});

MoorlProxyService.registerPlugin({
    entity: 'moorl_marker',
    listingRoute: 'moorl.marker.list',
    properties: [
        {name: 'name', visibility: 100},
        {name: 'type', visibility: 100},
        {name: 'className', visibility: 100},
    ],
    pluginName: 'MoorlFoundation',
    demoName: 'marker'
});

MoorlProxyService.registerPlugin({
    entity: 'moorl_sorting',
    listingRoute: 'moorl.sorting.list',
    properties: [
        {name: 'active', visibility: 100},
        {name: 'entity', visibility: 100},
        {name: 'label', visibility: 100},
        {name: 'priority', visibility: 100},
    ],
    pluginName: 'MoorlFoundation',
    demoName: 'marker'
});

MoorlProxyService.registerPlugin({entity: 'product', listingRoute: 'sw.product.index'});
MoorlProxyService.registerPlugin({entity: 'category', listingRoute: 'sw.category.index'});
MoorlProxyService.registerPlugin({entity: 'customer', listingRoute: 'sw.customer.index'});
MoorlProxyService.registerPlugin({entity: 'product_stream', listingRoute: 'sw.product.stream.index'});
MoorlProxyService.registerPlugin({entity: 'cms_page', listingRoute: 'sw.cms.index'});
MoorlProxyService.registerPlugin({entity: 'country', listingRoute: 'sw.country.index'});
MoorlProxyService.registerPlugin({entity: 'mail_template', listingRoute: 'sw.mail.template.index'});
