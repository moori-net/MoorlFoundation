const { Module } = Shopware;

import './page/list';
import './component/sw-admin-menu'
import './style/main.scss';

Module.register('moorl-foundation-article', {
    type: 'plugin',
    name: 'moorl-foundation-article.general.news',
    title: 'moorl-foundation-article.general.news',
    color: '#c4ea1d',
    entity: 'moorl_foundation_article',
    routes: {
        list: {
            component: 'moorl-foundation-article-list',
            path: 'list'
        }
    },
    navigation: [{
        label: 'moorl-foundation-article.general.news',
        color: '#c4ea1d',
        path: 'moorl.foundation.article.list',
        position: 40,
        parent: 'sw-dashboard'
    }]
});