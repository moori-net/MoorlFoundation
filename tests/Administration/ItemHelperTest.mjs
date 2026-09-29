import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';

const {default: cloneDeep} = await import(new URL(
    '../../../../../vendor/shopware/administration/Resources/app/administration/node_modules/lodash-es/cloneDeep.js',
    import.meta.url,
));
const {default: merge} = await import(new URL(
    '../../../../../vendor/shopware/administration/Resources/app/administration/node_modules/lodash-es/merge.js',
    import.meta.url,
));

globalThis.MoorlFoundation = {
    ModuleHelper: {
        getByEntity: () => ({labelProperty: 'title'}),
        getEntityMapping: () => ({
            content: {
                aiPromptSuggestions: [],
            },
            description: {
                aiPromptSuggestions: [{
                    snippetKey: 'moorl-example.ai.prompts.rewrite',
                    buttonSnippetKey: 'moorl-example.ai.promptLabels.rewrite',
                }],
            },
            metaKeywords: {
                hidden: true,
            },
            pluginText: {
                aiPromptSuggestions: ['moorl-example.ai.prompts.improve'],
            },
            notInContext: {
                aiPromptSuggestions: ['moorl-example.ai.prompts.ignored'],
            },
        }),
    },
};

globalThis.Shopware = {
    Utils: {
        object: {cloneDeep, merge},
    },
    EntityDefinition: {
        get: () => ({
            properties: {
                title: {type: 'string', flags: {required: true}},
                content: {type: 'html', flags: {}},
                description: {type: 'text', flags: {}},
                metaKeywords: {type: 'string', flags: {}},
                pluginText: {type: 'text', flags: {}},
                enabled: {type: 'bool', flags: {}},
                relatedProducts: {type: 'association', relation: 'one_to_many'},
                media: {type: 'association', relation: 'many_to_one', entity: 'media'},
                configuration: {type: 'json_object', flags: {}},
                internalNote: {type: 'string', flags: {write_protected: true}},
                customFields: {type: 'json_object', flags: {}},
            },
        }),
    },
};

const mappingSource = await readFile(new URL('../../src/Resources/app/administration/src/core/config/form-builder/mapping.js', import.meta.url), 'utf8');
const itemHelperSource = await readFile(new URL('../../src/Resources/app/administration/src/core/helper/item.helper.js', import.meta.url), 'utf8');
const source = `${mappingSource.replace('export default mapping;', '')}\n${itemHelperSource.replace(/^import mapping from .*?;\r?\n/m, '')}`;
const {default: ItemHelper} = await import(`data:text/javascript,${encodeURIComponent(source)}`);
const helper = new ItemHelper({componentName: 'moorl-example-detail', entity: 'moorl_example'});

assert.deepEqual(helper.getAiContext(), {
    entity: 'moorl_example',
    labelProperty: 'title',
    fields: {
        title: {type: 'string', required: true},
        content: {type: 'html', required: false},
        description: {type: 'text', required: false},
        metaKeywords: {type: 'string', required: false},
        pluginText: {type: 'text', required: false},
        enabled: {type: 'bool', required: false},
    },
});

assert.deepEqual(helper.getAiPromptSuggestions(), [
    {
        field: 'description',
        snippetKey: 'moorl-example.ai.prompts.rewrite',
        buttonSnippetKey: 'moorl-example.ai.promptLabels.rewrite',
    },
    {
        field: 'pluginText',
        snippetKey: 'moorl-example.ai.prompts.improve',
    },
]);

assert.deepEqual(helper.getAiImageUrls({
    media: {url: '/media/article-cover.jpg'},
    relatedProducts: [{cover: {url: '/media/related-product.jpg'}}],
}), ['/media/article-cover.jpg']);
assert.deepEqual(helper.getAssociations(), ['relatedProducts', 'media']);

console.log('ItemHelper AI context test passed.');
