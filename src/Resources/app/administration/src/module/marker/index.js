const { Module } = Shopware;
import './page/list';
import './page/detail';
import './page/create';

Module.register('moorl-marker', {
    type: 'plugin',
    name: 'MerchantFinder',
    title: 'moorl-marker.title',
    color: '#ff3d58',
    icon: 'regular-map-marker',
    routes: {
        list: {
            component: 'moorl-marker-list',
            path: 'list',
            meta: {
                parentPath: 'sw.settings.index.plugins'
            }
        },
        detail: {
            component: 'moorl-marker-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'moorl.marker.list',
            }
        },
        create: {
            component: 'moorl-marker-create',
            path: 'create',
            meta: {
                parentPath: 'moorl.marker.list'
            }
        }
    },
    settingsItem: [
        {
            name: 'moorl-marker-list',
            to: 'moorl.marker.list',
            group: 'plugins',
            icon: 'regular-map-marker',
            label: 'moorl-marker.title'
        }
    ]
});
