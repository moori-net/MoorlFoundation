const {Module} = Shopware;

import './page/index';

Module.register('moorl-demo-data', {
    type: 'plugin',
    name: 'moorl-demo-data',
    title: 'moorl-foundation.label.settingsDemoData',
    icon: 'regular-database',
    routes: {
        index: {
            component: 'moorl-demo-data-index',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index.plugins'
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
            label: 'moorl-foundation.label.settingsDemoData'
        }
    ]
});
