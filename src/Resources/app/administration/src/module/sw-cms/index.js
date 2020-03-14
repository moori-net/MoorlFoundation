const { Module } = Shopware;

import './component';
import deDE from './snippet/de-DE';
import enGB from './snippet/en-GB';

Module.register('moorl-cms', {
    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },
});

