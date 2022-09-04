const { Module } = Shopware;
import './page/list';
import './page/detail';
import './page/create';
import './style/main.scss';

Module.register('moorl-marker', {
    type: 'plugin',
    name: 'MerchantFinder',
    title: 'moorl-foundation.label.settingsMarker',
    color: '#ff3d58',
    icon: 'default-object-globe',
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
            icon: 'default-object-globe',
            label: 'moorl-foundation.label.settingsMarker'
        }
    ]
});
