import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';

const detailTemplate = await readFile(new URL('../../src/Resources/app/administration/src/abstract/page/detail/index.html.twig', import.meta.url), 'utf8');
const detailComponent = await readFile(new URL('../../src/Resources/app/administration/src/abstract/page/detail/index.js', import.meta.url), 'utf8');
const chatTemplate = await readFile(new URL('../../src/Resources/app/administration/src/component/ai-chat/index.html.twig', import.meta.url), 'utf8');

assert.match(detailTemplate, /<sw-sidebar :propagate-width="true">/);
assert.match(detailTemplate, /<sw-sidebar-item[\s\S]*?<moorl-ai-chat/);
const aiSidebarItem = detailTemplate.match(/<sw-sidebar-item[\s\S]*?<\/sw-sidebar-item>/)?.[0] ?? '';
assert.match(aiSidebarItem, /:disabled="isLoading"/);
assert.doesNotMatch(aiSidebarItem, /isNewItem|!item\.id/);
assert.doesNotMatch(detailTemplate, /moorl-ai-chat-modal/);
assert.doesNotMatch(detailComponent, /showAiChatModal/);
assert.doesNotMatch(chatTemplate, /<sw-modal/);
assert.match(chatTemplate, /class="moorl-ai-chat"/);

console.log('Detail AI sidebar test passed.');
