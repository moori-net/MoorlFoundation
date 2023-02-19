const { Module } = Shopware;
import './page/list';
import './page/detail';
import './page/create';

Module.register('moorl-client', {
    type: 'plugin',
    name: 'MerchantFinder',
    title: 'moorl-client.title',
    color: '#ff3d58',
    icon: 'default-object-globe',
    routes: {
        list: {
            component: 'moorl-client-list',
            path: 'list'
        },
        detail: {
            component: 'moorl-client-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'moorl.client.list'
            }
        },
        create: {
            component: 'moorl-client-create',
            path: 'create',
            meta: {
                parentPath: 'moorl.client.list'
            }
        }
    },
    settingsItem: [
        {
            name: 'moorl-client-list',
            to: 'moorl.client.list',
            group: 'plugins',
            icon: 'default-object-globe',
            label: 'moorl-client.title'
        }
    ]
});
