import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';

let component;
globalThis.Shopware = {
    Component: {
        register: (_name, definition) => {
            component = definition;
        },
    },
    Mixin: {
        getByName: () => ({}),
    },
    Utils: {
        get: () => undefined,
    },
};
globalThis.window = {location: {origin: 'https://admin.example.test'}};

const componentSource = `const template = '';\n${(await readFile(new URL('../../src/Resources/app/administration/src/component/ai-chat/index.js', import.meta.url), 'utf8'))
    .replace(/^import .*?;\r?\n/gm, '')}`;
await import(`data:text/javascript,${encodeURIComponent(componentSource)}`);

const requests = [];
const emitted = [];
let focused = 0;
let queriedSelector;
const messagesContainer = {
    scrollHeight: 1200,
    scrollTop: 0,
};
const state = {
    ...component.data(),
    item: {
        id: 'item-id',
        title: 'Unkorrigierter Text',
        relation: {id: 'read-only-context'},
    },
    itemHelper: {
        getAiContext: () => ({
            entity: 'moorl_example',
            labelProperty: 'title',
            fields: {title: {type: 'string', required: true}},
        }),
        getAiImageUrls: () => ['/media/article-cover.jpg'],
        getAiPromptSuggestions: () => [
            {
                field: 'content',
                snippetKey: 'moorl-foundation.ai.prompts.proofread',
                buttonSnippetKey: 'moorl-foundation.ai.promptLabels.proofread',
            },
            {
                field: 'pluginText',
                snippetKey: 'moorl-example.ai.prompts.missing',
            },
        ],
    },
    foundationApiService: {
        post: async (_path, payload) => {
            requests.push(JSON.parse(JSON.stringify(payload)));
            return {message: 'Korrigiert.', changes: {title: 'Korrigierter Text'}};
        },
    },
    $emit: (...args) => emitted.push(args),
    $tc: (key, replacements = {}, _amount) => ({
        'moorl-foundation.field.content': 'Content label',
        'moorl-foundation.ai.prompts.proofread': `Proofread ${replacements.field}.`,
        'moorl-foundation.ai.promptLabels.proofread': `Proofread ${replacements.field}`,
    }[key] ?? key),
    $refs: {
        messages: messagesContainer,
        messageInput: {
            $el: {
                querySelector: (selector) => {
                    queriedSelector = selector;

                    return {
                        focus: () => {
                            focused += 1;
                        },
                    };
                },
            },
        },
    },
    $nextTick: async () => {},
    createNotificationError: () => {},
    createNotificationSuccess: () => {},
};

for (const [name, method] of Object.entries(component.methods)) {
    state[name] = method.bind(state);
}

for (const [name, computed] of Object.entries(component.computed)) {
    state[name] = computed.bind(state);
}

assert.deepEqual(state.aiPromptSuggestions(), [{
    field: 'content',
    prompt: 'Proofread content.',
    label: 'Proofread Content label',
}]);

state.messageInput = 'Existing draft';
state.onSelectPromptSuggestion(state.aiPromptSuggestions()[0]);

assert.equal(state.messageInput, 'Proofread content.');
assert.equal(focused, 1);
assert.equal(queriedSelector, 'textarea');
assert.deepEqual(requests, []);
assert.deepEqual(emitted, []);

let preventedEnterEvents = 0;
state.sendOnEnter = false;
state.messageInput = 'Do not send this';
await state.onEnterKeydown({
    preventDefault: () => {
        preventedEnterEvents += 1;
    },
});

assert.equal(preventedEnterEvents, 0);
assert.equal(state.messageInput, 'Do not send this');
assert.deepEqual(requests, []);

state.sendOnEnter = true;
state.messageInput = 'Bitte korrekturlesen.';
await state.onEnterKeydown({
    preventDefault: () => {
        preventedEnterEvents += 1;
    },
});

assert.equal(preventedEnterEvents, 1);
assert.equal(messagesContainer.scrollTop, messagesContainer.scrollHeight);

assert.deepEqual(requests, [{
    messages: [
        {
            role: 'context',
            content: [{
                type: 'entity_context',
                context: {
                    entity: 'moorl_example',
                    labelProperty: 'title',
                    fields: {title: {type: 'string', required: true}},
                },
            }],
        },
        {
            role: 'user',
            content: [{type: 'text', text: 'Bitte korrekturlesen.'}],
        },
    ],
    item: {
        id: 'item-id',
        title: 'Unkorrigierter Text',
        relation: {id: 'read-only-context'},
    },
    images: [{url: 'https://admin.example.test/media/article-cover.jpg'}],
}]);
assert.deepEqual(emitted, [['apply-changes', {title: 'Korrigierter Text'}]]);

console.log('AI chat modal context test passed.');
