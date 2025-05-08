import './main.scss';

import './core';
import './store';
import './init';
import './mixin';
import './abstract';
import './component';
import './extension';
import './module';

MoorlFoundation.ModuleHelper.registerModule({
    entity: 'moorl_client',
    name: 'moorl-client',
    icon: 'regular-sign-in',
    properties: [
        {name: 'active', visibility: 100},
        {name: 'name', visibility: 100},
        {name: 'type', visibility: 100},
    ]
});

MoorlFoundation.ModuleHelper.registerModule({
    entity: 'moorl_cms_element_config',
    name: 'moorl-cms-element-config',
    icon: 'regular-layout',
    properties: [
        {name: 'name', visibility: 100},
        {name: 'type', visibility: 100},
    ],
    entityMapping: {
        autoSize: {hidden: true},
        type: {hidden: true},
        config: {
            componentName: 'moorl-cms-slot-card',
            tab: 'general',
            card: undefined,
            attributes: {
                item: ({item}) => item,
            },
            order: 500
        }
    }
});

MoorlFoundation.ModuleHelper.registerModule({
    entity: 'moorl_marker',
    name: 'moorl-marker',
    icon: 'regular-map-marker',
    properties: [
        {name: 'name', visibility: 100},
        {name: 'type', visibility: 100},
        {name: 'className', visibility: 100},
    ],
    pluginName: 'MoorlFoundation',
    demoName: 'marker'
});

MoorlFoundation.ModuleHelper.registerModule({
    entity: 'moorl_sorting',
    name: 'moorl-sorting',
    icon: 'regular-sort',
    properties: [
        {name: 'active', visibility: 100},
        {name: 'entity', visibility: 100},
        {name: 'label', visibility: 100},
        {name: 'priority', visibility: 100},
    ]
});

MoorlFoundation.ModuleHelper.registerModule({
    entity: 'moorl_media',
    name: 'moorl-media',
    properties: [
        {name: 'active', visibility: 100},
        {name: 'name', visibility: 100},
        {name: 'technicalName', visibility: 100},
        {name: 'type', visibility: 100}
    ],
    entityMapping: {
        type: {
            tab: 'general',
            card: 'media',
            componentName: 'moorl-select-field',
            attributes: {
                customSet: ['auto', 'embedded', 'media', 'vimeo', 'youtube'],
                snippetPath: 'moorl-foundation.field'
            }
        },
        duration: {tab: 'general', card: 'media'},
        embeddedUrl: {
            conditions: [{property: 'type', value: 'media', operator: '!='}],
        },
        embeddedId: {
            conditions: [{property: 'type', value: 'media', operator: '!='}],
        },
        media: {
            conditions: [{property: 'type', value: 'media', operator: 'eq'}],
        },
        config: {
            mapping: MoorlFoundation.fieldsetsConfig['media-config']
        }
    },
    pluginName: 'MoorlFoundation',
    demoName: 'import-export'
});

MoorlFoundation.ModuleHelper.registerModule({
    entity: 'moorl_media_video',
    properties: [
        {name: 'media.fileName', visibility: 100, mediaProperty: 'media'},
        {name: 'media.mimeType', visibility: 100},
        {name: 'minWidth', visibility: 200},
    ]
});

MoorlFoundation.ModuleHelper.registerModule({entity: 'product', listPath: 'sw.product.index'});
MoorlFoundation.ModuleHelper.registerModule({entity: 'category', listPath: 'sw.category.index'});
MoorlFoundation.ModuleHelper.registerModule({entity: 'customer', listPath: 'sw.customer.index'});
MoorlFoundation.ModuleHelper.registerModule({entity: 'product_stream', listPath: 'sw.product.stream.index'});
MoorlFoundation.ModuleHelper.registerModule({entity: 'cms_page', listPath: 'sw.cms.index'});
MoorlFoundation.ModuleHelper.registerModule({entity: 'country', listPath: 'sw.country.index'});
MoorlFoundation.ModuleHelper.registerModule({entity: 'mail_template', listPath: 'sw.mail.template.index'});
