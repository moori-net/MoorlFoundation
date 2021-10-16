const {Module} = Shopware;

import './page/demo-data';
import './page/cms-element-config';

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
        },
        cmselementconfig: {
            component: 'moorl-cms-element-config',
            path: 'cmselementconfig',
            meta: {
                parentPath: 'sw.settings.index'
            },
        }
    },

    settingsItem: [
        {
            privilege: 'system.system_config',
            name: 'moorl-foundation-settings-demo-data',
            to: 'moorl.foundation.settings.demodata',
            group: 'plugins',
            icon: 'default-package-gift',
            label: 'moorl-foundation.label.settingsDemoData'
        },
        {
            privilege: 'system.system_config',
            name: 'moorl-cms-element-config',
            to: 'moorl.foundation.settings.cmselementconfig',
            group: 'plugins',
            icon: 'default-package-gift',
            label: 'moorl-foundation.label.settingsCmsElementConfig'
        }
    ]
});
