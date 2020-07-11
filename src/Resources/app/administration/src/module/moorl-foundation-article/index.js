const { Module } = Shopware;

import './page/list';
import './component/sw-admin-menu'
import './style/main.scss';

Module.register('moorl-foundation-article', {
    type: 'plugin',
    name: 'moorl-cms.general.news',
    title: 'moorl-cms.general.news',
    color: '#ff3d58',
    entity: 'moorl_foundation_article',
    routes: {
        list: {
            component: 'moorl-foundation-article-list',
            path: 'list'
        }
    },
    navigation: [{
        label: 'moorl-cms.general.news',
        color: '#ff3d58',
        path: 'moorl.foundation.article.list',
        position: 40,
        parent: 'sw-dashboard'
    }]
});