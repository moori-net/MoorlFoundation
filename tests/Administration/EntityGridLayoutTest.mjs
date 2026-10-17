import assert from 'node:assert/strict';
import { test } from 'node:test';
import { existsSync, mkdtempSync, readFileSync, writeFileSync, rmSync } from 'node:fs';
import { createRequire } from 'node:module';
import { tmpdir } from 'node:os';
import { dirname, join, resolve, sep } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { promisify } from 'node:util';
import { execFile } from 'node:child_process';

const adminRoot = fileURLToPath(new URL('../../../../../vendor/shopware/administration/Resources/app/administration/', import.meta.url));
const require = createRequire(join(adminRoot, 'package.json'));
const chrome = [process.env.CHROME_BIN, 'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe', '/usr/bin/chromium', '/usr/bin/google-chrome']
    .find(path => path && existsSync(path));

test('wide tables scroll inside grid cards instead of expanding beyond the clipped card', { skip: !chrome }, async () => {
    const sass = require('sass');
    const card = readFileSync(join(adminRoot, 'node_modules/@shopware-ag/meteor-component-library/src/components/layout/mt-card/mt-card.vue'), 'utf8');
    const meteorCss = card.match(/<style scoped>([\s\S]*?)<\/style>/)[1];
    const gridScss = fileURLToPath(new URL('../../src/Resources/app/administration/src/component/grid/entity-grid-v2/index.scss', import.meta.url));
    const nativeGridScss = join(adminRoot, 'src/app/component/data-grid/sw-data-grid/sw-data-grid.scss');
    const css = sass.compileString(`@import '~scss/variables'; @import '${nativeGridScss.replaceAll('\\', '/')}'; @import '${gridScss.replaceAll('\\', '/')}';`, {
        importers: [{ findFileUrl: url => url.startsWith('~scss/') ? pathToFileURL(join(adminRoot, 'src/app/assets/scss', `${url.slice(6)}.scss`)) : null }],
        logger: { warn() {} },
    }).css;
    const directory = mkdtempSync(join(tmpdir(), 'moorl-grid-layout-'));
    assert.equal(dirname(resolve(directory)), resolve(tmpdir()));
    try {
        const file = join(directory, 'grid.html');
        writeFileSync(file, `<!doctype html><html><head><style>${meteorCss}\n${css}</style></head><body>
            <div id="container" style="width:360px">
                <article class="mt-card mt-card--grid moorl-entity-grid-v2"><div class="mt-card__content">
                    <div class="moorl-entity-grid-v2"><div class="sw-data-grid"><div class="sw-data-grid__wrapper">
                        <table class="sw-data-grid__table" style="min-width:1440px"><tbody><tr>
                            <td>First column</td><td style="text-align:right"><span id="last">Last column</span></td>
                        </tr></tbody></table>
                    </div></div></div>
                </div></article>
            </div><output id="result"></output>
            <script>
                const results = [320, 760].map(width => {
                    document.querySelector('#container').style.width = width + 'px';
                    const wrapper = document.querySelector('.sw-data-grid__wrapper');
                    wrapper.scrollLeft = 99999;
                    return { width, viewport: wrapper.clientWidth, content: wrapper.scrollWidth, scrollLeft: wrapper.scrollLeft,
                        lastVisible: document.querySelector('#last').getBoundingClientRect().right <= wrapper.getBoundingClientRect().right + 1 };
                });
                document.querySelector('#result').textContent = JSON.stringify(results);
            </script></body></html>`);
        const { stdout } = await promisify(execFile)(chrome, ['--headless', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
            `--user-data-dir=${join(directory, 'profile')}`, '--dump-dom', pathToFileURL(file).href], { windowsHide: true, timeout: 30000, maxBuffer: 1024 * 1024 });
        const results = JSON.parse(stdout.match(/<output id="result">([^<]+)<\/output>/)[1]);
        for (const result of results) {
            assert.ok(result.viewport <= result.width, `The ${result.width}px card must constrain the scroll viewport, got ${result.viewport}px.`);
            assert.ok(result.content > result.viewport, 'The wide table must overflow its scroll viewport.');
            assert.ok(result.scrollLeft > 0, 'The native table wrapper must scroll horizontally.');
            assert.equal(result.lastVisible, true, 'Scrolling must reveal the last column.');
        }
    } finally {
        assert.ok(resolve(directory).startsWith(resolve(tmpdir()) + sep));
        rmSync(directory, { recursive: true, force: true });
    }
});
