import './page/index';

Shopware.Module.register('moorl-demo-data', {
    type: 'plugin',
    name: 'moorl-demo-data',
    title: 'moorl-demo-data.name',
    icon: 'regular-database',
    color: '#000000',
    routes: {
        index: {
            component: 'moorl-demo-data-index',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index.plugins',
                privilege: 'system.system_config'
            },
        },
    },
    settingsItem: [
        {
            privilege: 'system.system_config',
            name: 'moorl-demo-data-index',
            to: 'moorl.demo.data.index',
            group: 'plugins',
            icon: 'regular-database',
            label: 'moorl-demo-data.name',
        },
    ],
});
