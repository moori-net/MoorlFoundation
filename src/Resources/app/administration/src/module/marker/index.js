const { Module } = Shopware;
import './page/list';
import './page/detail';
import './page/create';

Module.register('moorl-marker', {
    type: 'plugin',
    name: 'MerchantFinder',
    title: 'moorl-marker.title',
    color: '#ff3d58',
    icon: 'regular-globe',
    routes: {
        list: {
            component: 'moorl-marker-list',
            path: 'list'
        },
        detail: {
            component: 'moorl-marker-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'moorl.marker.list'
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
            icon: 'regular-globe',
            label: 'moorl-marker.title'
        }
    ]
});
