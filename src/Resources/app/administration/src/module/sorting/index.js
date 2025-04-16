import './component/sorting-option-criteria-grid';
import './page/list';
import './page/detail';
import './page/create';

Shopware.Module.register('moorl-sorting', {
    type: 'plugin',
    name: 'settings-listing',
    title: 'moorl-sorting.general.mainMenuItemGeneral',
    icon: 'regular-sort',
    routes: {
        list: {
            component: 'moorl-sorting-list',
            path: 'list',
            meta: {
                parentPath: 'sw.settings.index.plugins'
            }
        },
        detail: {
            component: 'moorl-sorting-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'moorl.sorting.list'
            }
        },
        create: {
            component: 'moorl-sorting-create',
            path: 'create',
            meta: {
                parentPath: 'moorl.sorting.list'
            }
        }
    },
    settingsItem: [
        {
            privilege: 'system.system_config',
            to: 'moorl.sorting.list',
            group: 'plugins',
            icon: 'regular-sort'
        }
    ]
});
