import './page/index';

Shopware.Module.register('moorl-cms-element-config', {
    type: 'plugin',
    name: 'moorl-cms-element-config',
    title: 'moorl-foundation.label.settingsCmsElementConfig',
    icon: 'regular-layout',
    routes: {
        index: {
            component: 'moorl-cms-element-config-index',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index.plugins'
            },
        }
    },
    settingsItem: [
        {
            privilege: 'system.system_config',
            name: 'moorl-cms-element-config',
            to: 'moorl.cms.element.config.index',
            group: 'plugins',
            icon: 'regular-layout',
            label: 'moorl-foundation.label.settingsCmsElementConfig'
        }
    ]
});
