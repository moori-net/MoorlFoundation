# Context menus and filters in Administration lists

`moorl-entity-grid-v2` and `moorl-entity-grid-card-v2` accept additional row actions through the `contextMenuItems` prop. Actions can also be configured centrally for an entity under `componentConfig.list.contextMenuItems` in `ModuleHelper.registerModule()`. An explicit prop takes precedence; `[]` hides the additional actions. Standard actions remain available.

```js
componentConfig: {
    list: {
        contextMenuItems: [{
            name: 'publish',
            label: 'my-plugin.actions.publish',
            privilege: 'my_entity:update',
            variant: 'danger', // optional
            visible: item => item.status !== 'archived', // optional
            disabled: item => item.status === 'published', // optional, also accepts a Boolean
            action: async (item, grid) => {
                await publishItem(item.id);
                await grid.getItems();
            },
        }],
    },
}
```

`label` is a translation key. The grid disables additional actions while an asynchronous action runs. Unhandled errors produce an error notification. `privilege` is checked when displaying and invoking an action. Existing `customAction` configurations remain supported.

Use the additive `actions` slot for custom menu components, including through the card:

```twig
<moorl-entity-grid-card-v2 entity="my_entity">
    <template #actions="{ item, reload }">
        <sw-context-menu-item @click="publishAndReload(item, reload)">
            {{ $t('my-plugin.actions.publish') }}
        </sw-context-menu-item>
    </template>
</moorl-entity-grid-card-v2>
```

The slot receives the row `item` and the loading function `reload`. The caller manages permissions and busy states for custom slot components.

The `filters` prop accepts an array of Shopware Criteria filters. These complement the filters derived from `defaultItem` without changing defaults for newly created records. Changes to `filters` or `defaultItem` reload the first page. This supports order and customer lists without overriding the grid component.
