import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';

let component;
globalThis.Shopware = {
    Data: {Criteria: class {}},
    Component: {
        register: (_name, definition) => {
            component = definition;
        },
    },
    Mixin: {
        getByName: () => ({}),
    },
};

const componentSource = `const template = '';\n${(await readFile(new URL('../../src/Resources/app/administration/src/abstract/page/detail/index.js', import.meta.url), 'utf8'))
    .replace(/^import .*?;\r?\n/gm, '')}`;
await import(`data:text/javascript,${encodeURIComponent(componentSource)}`);

const notifications = [];
const state = {
    item: {
        title: 'Before',
        customFields: {protected: true},
    },
    itemHelper: {
        getAiContext: () => ({fields: {title: {type: 'string', required: true}}}),
    },
    $tc: (key) => key,
    createNotificationSuccess: (notification) => notifications.push(notification),
};

component.methods.onApplyAiChanges.call(state, {
    title: 'After',
    customFields: {invalid: true},
});

assert.deepEqual(state.item, {
    title: 'Before',
    customFields: {protected: true},
});
assert.deepEqual(notifications, []);

console.log('Detail AI changes validation test passed.');
