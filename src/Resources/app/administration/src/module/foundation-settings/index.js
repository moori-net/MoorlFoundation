const {Module} = Shopware;

import './page/demo-data';

Module.register('moorl-foundation-settings', {
    type: 'plugin',
    name: 'moorl-foundation-settings',
    title: 'moorl-foundation.label.settingsTitle',

    routes: {
        demodata: {
            component: 'moorl-foundation-settings-demo-data',
            path: 'demodata',
            meta: {
                parentPath: 'sw.settings.index'
            },
        }
    },

    settingsItem: [
        {
            name: 'moorl-foundation-settings-demo-data',
            to: 'moorl.foundation.settings.demodata',
            group: 'plugins',
            icon: 'default-package-gift',
            label: 'moorl-foundation.label.settingsDemoData'
        }
    ]
});
