import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { createRequire } from 'node:module';
import { test } from 'node:test';
import { fileURLToPath } from 'node:url';

const require = createRequire(new URL('../../../../../vendor/shopware/administration/Resources/app/administration/package.json', import.meta.url));
const { default: Criteria } = require(fileURLToPath(new URL('../../../../../vendor/shopware/administration/Resources/app/administration/node_modules/@shopware-ag/meteor-admin-sdk/umd/data/Criteria.js', import.meta.url)));
let component;
let definition;
globalThis.Shopware = {
    Data: { Criteria, EntityCollection: class extends Array {} }, Context: { api: {} },
    EntityDefinition: { get: () => definition }, Mixin: { getByName: () => ({}) },
    Component: { register: (_, config) => { component = config; } },
};
const source = await readFile(new URL('../../src/Resources/app/administration/src/component/grid/moorl-csv-import/index.js', import.meta.url), 'utf8');
await import(`data:text/javascript,${encodeURIComponent(`const template = '';\n${source.replace(/^import .*;\r?\n/gm, '')}`)}`);
const channel = 'a'.repeat(32);
const otherChannel = 'b'.repeat(32);
const typeId = 'c'.repeat(32);

function schema() {
    return { properties: {
        id: { type: 'uuid', flags: { primary_key: true, required: true } },
        name: { type: 'string', flags: { required: true } },
        salesChannelId: { type: 'uuid', flags: { required: true } },
        salesChannel: { type: 'association', relation: 'many_to_one', entity: 'sales_channel', localField: 'salesChannelId', flags: {} },
        languageId: { type: 'uuid', flags: { required: true } },
        language: { type: 'association', relation: 'many_to_one', entity: 'language', localField: 'languageId', flags: {} },
        typeId: { type: 'uuid', flags: { required: true, immutable: true } },
        type: { type: 'association', relation: 'many_to_one', entity: 'moorl_tv_ticket_type', localField: 'typeId', flags: {} },
        customerId: { type: 'uuid', flags: {} },
        customer: { type: 'association', relation: 'many_to_one', entity: 'customer', localField: 'customerId', flags: {} },
        managedId: { type: 'uuid', flags: { required: true, write_protected: true } },
        managed: { type: 'association', relation: 'many_to_one', entity: 'managed', localField: 'managedId', flags: {} },
        versionId: { type: 'uuid', flags: { required: true } },
        record: { type: 'association', relation: 'one_to_one', entity: 'record', localField: 'id', flags: {} },
        tags: { type: 'association', relation: 'many_to_many', entity: 'tag', localField: 'id', referenceField: 'id', flags: { required: true } },
        children: { type: 'association', relation: 'one_to_many', entity: 'child', flags: {} },
    } };
}

function state(defaultItem = {}) {
    definition = schema();
    const originalDefinition = structuredClone(definition);
    const saved = [];
    const repository = { create: () => ({}), route: '/test', entityName: 'test', search: async () => ({ first: () => null }) };
    const value = {
        ...component.data(), ...component.methods, entity: 'test', defaultItem,
        repository, repositoryFactory: { create: () => repository }, $tc: key => key,
        onFinishImport() {},
        saveItem: item => saved.push(item), saved, originalDefinition,
    };
    value.initEditColumns();
    value.initSelectedItem();
    return value;
}

test('required foreign keys get one ID column with the referenced entity, without mutating the schema', () => {
    const instance = state();
    instance.initEditColumns();
    assert.deepEqual(definition, instance.originalDefinition);
    assert.deepEqual(instance.requiredColumns.map(column => [column.property, column.referenceEntity]), [
        ['salesChannelId', 'sales_channel'], ['languageId', 'language'], ['typeId', 'moorl_tv_ticket_type'],
    ]);
    assert.equal(instance.columns.filter(column => column.property === 'salesChannelId').length, 1);
    assert.equal(instance.columns.some(column => column.property === 'salesChannel'), false);
    assert.equal(instance.columns.some(column => column.property === 'managedId'), false);
});

test('fixed grid defaults do not get another editable selector and no empty association defaults are generated', () => {
    const instance = state({ typeId });
    assert.equal(instance.columns.some(column => column.property === 'typeId'), false);
    assert.deepEqual(instance.requiredColumns.map(column => column.property), ['salesChannelId', 'languageId']);
    assert.deepEqual(instance.selectedItem, { typeId });
});

test('required one-to-one foreign keys are detected while an entity primary key is excluded', () => {
    const instance = state();
    definition.properties.salesChannel.relation = 'one_to_one';
    instance.initEditColumns();
    assert.equal(instance.requiredColumns.find(column => column.property === 'salesChannelId')?.referenceEntity, 'sales_channel');
    assert.equal(instance.requiredColumns.some(column => column.property === 'id'), false);
});

test('an import-wide sales channel fills missing or blank CSV values while row values take priority', async () => {
    const instance = state({ typeId });
    instance.selectedItem.salesChannelId = channel;
    instance.mapping = { name: 'Name', salesChannelId: 'Channel' };
    for (const Channel of [undefined, '', '   ', otherChannel.toUpperCase()]) {
        const row = await instance.sanitizeItem({ Name: 'Guest', Channel });
        await instance.prepareSaveItem(row);
    }
    assert.equal(instance.errorCount, 0);
    assert.deepEqual(instance.saved.map(item => item.salesChannelId), [channel, channel, channel, otherChannel]);
    assert.ok(instance.saved.every(item => item.typeId === typeId));
});

test('cleared defaults never write null or empty values over DAL defaults or existing values', async () => {
    const instance = state();
    instance.selectedItem = { salesChannelId: null, languageId: undefined, customerId: '', name: '   ', enabled: false, count: 0 };
    await instance.prepareSaveItem({ id: typeId });
    assert.deepEqual(instance.saved, [{ id: typeId, enabled: false, count: 0 }]);
});

test('fixed grid context takes priority over selected defaults and CSV data', async () => {
    const instance = state({ typeId });
    instance.selectedItem.typeId = channel;
    await instance.prepareSaveItem({ typeId: otherChannel, name: 'Guest' });
    assert.equal(instance.saved[0].typeId, typeId);
});

test('invalid CSV IDs cannot be replaced by a selected default when pause on error is disabled', async () => {
    const instance = state();
    instance.selectedItem.salesChannelId = channel;
    instance.mapping = { name: 'Name', salesChannelId: 'Channel' };
    instance.data = [{ Name: 'Guest', Channel: 'invalid-id' }];
    instance.options.pause = false;
    await instance.importItem();
    assert.equal(instance.errorCount, 1);
    assert.deepEqual(instance.saved, []);
    assert.equal(instance.step, 4, 'An invalid row is skipped when pause on error is disabled.');
});

test('after an invalid row the next row is imported with defaults when pause on error is disabled', async () => {
    const instance = state();
    instance.selectedItem.salesChannelId = channel;
    instance.mapping = { name: 'Name', salesChannelId: 'Channel' };
    instance.data = [{ Name: 'Invalid guest', Channel: 'invalid-id' }, { Name: 'Valid guest', Channel: '' }];
    instance.options.pause = false;
    await instance.importItem();
    assert.deepEqual(instance.saved, [{ salesChannelId: channel, name: 'Valid guest' }]);
    assert.equal(instance.errorCount, 1);
});

test('the wizard renders required foreign key selects and their choices reach the imported payload', async () => {
    const vue = require('vue');
    const { compile } = createRequire(require.resolve('vue'))('@vue/compiler-dom');
    const { renderToString } = require('@vue/server-renderer');
    const template = await readFile(new URL('../../src/Resources/app/administration/src/component/grid/moorl-csv-import/index.html.twig', import.meta.url), 'utf8');
    const render = new Function('Vue', compile(template, { prefixIdentifiers: true }).code)(vue);
    definition = schema();
    const selects = new Map();
    const saved = [];
    let instance;
    const repository = { create: () => ({}), search: async () => [] };
    const app = vue.createSSRApp({
        ...component, render,
        created() {
            instance = this;
            this.initEditColumns();
            this.initSelectedItem();
            this.step = 2;
        },
        methods: { ...component.methods, getItemByUniqueProperties: async () => null, saveItem: item => saved.push(item) },
    }, { entity: 'test', defaultItem: { typeId }, onCloseModal() {}, onFinishImport() {} });
    app.provide('repositoryFactory', { create: () => repository });
    app.provide('context', {});
    app.provide('mediaService', {});
    app.config.globalProperties.$t = app.config.globalProperties.$tc = key => key;
    const passThrough = { setup: (_, { slots }) => () => vue.h('div', slots.default?.()) };
    for (const tag of ['sw-modal', 'sw-container', 'mt-switch', 'moorl-select-field', 'mt-button', 'sw-file-input', 'mt-progress-bar']) app.component(tag, passThrough);
    app.component('sw-entity-single-select', {
        props: ['entity', 'value'], emits: ['update:value'],
        setup(props, { emit }) {
            selects.set(props.entity, { choose: id => emit('update:value', id) });
            return () => vue.h('input', { 'data-entity': props.entity });
        },
    });
    const html = await renderToString(app);
    assert.match(html, /data-entity="sales_channel"/);
    assert.match(html, /data-entity="language"/);
    assert.equal(selects.has('moorl_tv_ticket_type'), false, 'The grid already supplies the type.');
    assert.equal(selects.has('customer'), false, 'Optional foreign keys do not require a default selector.');
    selects.get('sales_channel').choose(channel);
    await instance.prepareSaveItem({ name: 'Imported guest' });
    assert.deepEqual(saved[0], { typeId, salesChannelId: channel, name: 'Imported guest' });
    selects.get('sales_channel').choose(null);
    await instance.prepareSaveItem({ name: 'Guest with a channel in CSV', salesChannelId: otherChannel });
    assert.equal(saved[1].salesChannelId, otherChannel);
    assert.equal(Object.hasOwn(saved[1], 'languageId'), false, 'Leave the DAL language default available.');
});
