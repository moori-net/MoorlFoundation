import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { createRequire } from 'node:module';
import { test } from 'node:test';
import { fileURLToPath } from 'node:url';

const require = createRequire(new URL('../../../../../vendor/shopware/administration/Resources/app/administration/package.json', import.meta.url));
const { default: Criteria } = require(fileURLToPath(new URL('../../../../../vendor/shopware/administration/Resources/app/administration/node_modules/@shopware-ag/meteor-admin-sdk/umd/data/Criteria.js', import.meta.url)));
let grid;
globalThis.Shopware = {
    Data: { Criteria },
    Mixin: { getByName: () => ({}) },
    Component: { register: (_, config) => { grid = config; } },
};
const configured = [{ name: 'default' }];
globalThis.MoorlFoundation = { ModuleHelper: { getByEntity: () => ({ componentConfig: { list: { contextMenuItems: configured } } }) } };
const source = await readFile(new URL('../../src/Resources/app/administration/src/component/grid/entity-grid-v2/index.js', import.meta.url), 'utf8');
await import(`data:text/javascript,${encodeURIComponent(source.replace(/^import .*;\r?\n/gm, '').replace('    template,', '    template: "",'))}`);

test('additional filters never accumulate in the cached listing criteria', async () => {
    const criteria = new Criteria(1, 10).addFilter(Criteria.equals('saleId', 'event-a'));
    const requests = [];
    const context = {
        itemCriteria: criteria,
        filters: [Criteria.equals('customerId', 'customer-a')],
        loadItems: async value => { requests.push(value.parse()); },
    };
    await grid.methods.getItems.call(context);
    await grid.methods.getItems.call(context);
    const expected = [
        { type: 'equals', field: 'saleId', value: 'event-a' },
        { type: 'equals', field: 'customerId', value: 'customer-a' },
    ];
    assert.deepEqual(requests.map(({ filter }) => filter), [expected, expected]);
    assert.deepEqual(criteria.parse().filter, [expected[0]]);
});

test('explicit menu configuration overrides entity defaults, including an empty menu', () => {
    assert.equal(grid.computed.configuredContextMenuItems.call({ entity: 'example' }), configured);
    assert.deepEqual(grid.computed.configuredContextMenuItems.call({ entity: 'example', contextMenuItems: [] }), []);
    const actions = [{ name: 'allowed' }, { name: 'restricted', privilege: 'example:update' },
        { name: 'hidden', visible: item => item.active }];
    assert.deepEqual(grid.methods.getContextMenuItems.call({ configuredContextMenuItems: actions, acl: { can: () => false } }, { active: false }), [actions[0]]);
});

test('menu actions stay locked until completion and recover from rejected actions', async () => {
    let complete;
    let calls = 0;
    const errors = [];
    const item = { id: 'item-a' };
    const context = {
        contextActionBusy: false,
        acl: { can: () => true },
        isContextMenuItemDisabled: grid.methods.isContextMenuItemDisabled,
        createNotificationError: ({ message }) => errors.push(message),
    };
    const action = { action: async (row, instance) => {
        assert.equal(row, item);
        assert.equal(instance, context);
        calls++;
        await new Promise(resolve => { complete = resolve; });
    } };
    const running = grid.methods.onContextMenuItem.call(context, action, item);
    await grid.methods.onContextMenuItem.call(context, action, item);
    assert.equal(calls, 1);
    assert.equal(context.contextActionBusy, true);
    complete();
    await running;
    assert.equal(context.contextActionBusy, false);
    await grid.methods.onContextMenuItem.call(context, { action: async () => { throw new Error('Request failed'); } }, item);
    assert.deepEqual(errors, ['Request failed']);
    assert.equal(context.contextActionBusy, false);
});
