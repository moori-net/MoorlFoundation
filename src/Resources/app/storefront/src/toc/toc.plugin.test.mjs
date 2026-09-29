import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

test('observes h2 through h6 headings for active TOC links', async () => {
    let observedSelector = null;
    const window = {
        PluginBaseClass: class {},
        addEventListener() {},
        location: { hash: '' },
    };
    const document = {
        querySelector(selector) {
            assert.equal(selector, '.cms-page');

            return {
                querySelectorAll(selector) {
                    observedSelector = selector;

                    return [];
                },
            };
        },
    };
    const source = await readFile(new URL('./toc.plugin.js', import.meta.url), 'utf8');
    const TocPlugin = new Function(
        'window',
        'document',
        source.replace('export default class', 'return class'),
    )(window, document);

    (new TocPlugin()).init();

    assert.equal(observedSelector, 'h2,h3,h4,h5,h6');
});
