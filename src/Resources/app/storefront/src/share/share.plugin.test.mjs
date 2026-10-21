import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

async function createShare({ share, options = {} } = {}) {
    const source = await readFile(new URL('./share.plugin.js', import.meta.url), 'utf8');
    const SharePlugin = new Function('window', 'document', 'navigator',
        source.replace('export default class', 'return class'),
    )({ PluginBaseClass: class {}, location: { href: 'https://example.com/event' } },
        { title: 'Event title' }, { share });
    const el = new EventTarget();
    const classes = new Set(['d-none']);
    el.classList = {
        add: (name) => classes.add(name),
        remove: (name) => classes.delete(name),
        contains: (name) => classes.has(name),
    };
    const plugin = new SharePlugin();
    plugin.el = el;
    plugin.options = { ...SharePlugin.options, ...options };
    plugin.init();
    return { el, plugin };
}

test('any marked element shares the current page and prevents link navigation', async () => {
    let shared;
    const { el } = await createShare({ share: async (data) => { shared = data; } });
    const click = new Event('click', { cancelable: true });
    el.dispatchEvent(click);
    assert.deepEqual(shared, { title: 'Event title', url: 'https://example.com/event' });
    assert.equal(click.defaultPrevented, true);
    assert.equal(el.classList.contains('d-none'), false);
});

test('configured title, URL and text are passed to the native share API', async () => {
    let shared;
    const { el } = await createShare({
        options: { title: 'Concert', url: 'https://example.com/concert', text: 'Join us!' },
        share: async (data) => { shared = data; },
    });
    el.dispatchEvent(new Event('click', { cancelable: true }));
    assert.deepEqual(shared, { title: 'Concert', url: 'https://example.com/concert', text: 'Join us!' });
});

test('the marked element stays hidden when native sharing is unsupported', async () => {
    const { el } = await createShare();
    assert.equal(el.classList.contains('d-none'), true);
    const click = new Event('click', { cancelable: true });
    el.dispatchEvent(click);
    assert.equal(click.defaultPrevented, false);
});

test('cancelled or rejected native sharing is handled without an unhandled rejection', async () => {
    const { el } = await createShare({ share: async () => { throw new Error('Cancelled'); } });
    el.dispatchEvent(new Event('click', { cancelable: true }));
    await new Promise((resolve) => setImmediate(resolve));
});
