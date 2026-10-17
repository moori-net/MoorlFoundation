# Kontextmenü und Filter in Administration-Listen

`moorl-entity-grid-v2` und `moorl-entity-grid-card-v2` unterstützen zusätzliche Zeilenaktionen über die Eigenschaft `contextMenuItems`. Alternativ lassen sich die Aktionen zentral für eine Entity in `ModuleHelper.registerModule()` unter `componentConfig.list.contextMenuItems` hinterlegen. Eine explizite Eigenschaft hat Vorrang; `[]` blendet die zusätzlichen Aktionen aus. Die Standardaktionen bleiben erhalten.

```js
componentConfig: {
    list: {
        contextMenuItems: [{
            name: 'publish',
            label: 'my-plugin.actions.publish',
            privilege: 'my_entity:update',
            variant: 'danger', // optional
            visible: item => item.status !== 'archived', // optional
            disabled: item => item.status === 'published', // optional, auch Boolean
            action: async (item, grid) => {
                await publishItem(item.id);
                await grid.getItems();
            },
        }],
    },
}
```

`label` ist ein Übersetzungsschlüssel. Während eines asynchronen Aufrufs sperrt das Grid die zusätzlichen Aktionen. Nicht abgefangene Fehler erscheinen als Fehlerbenachrichtigung. `privilege` wird beim Anzeigen und beim Ausführen geprüft. Bestehende Konfigurationen mit `customAction` bleiben verfügbar.

Für eigene Menükomponenten gibt es den ergänzenden Slot `actions`, auch über die Karte:

```twig
<moorl-entity-grid-card-v2 entity="my_entity">
    <template #actions="{ item, reload }">
        <sw-context-menu-item @click="publishAndReload(item, reload)">
            {{ $t('my-plugin.actions.publish') }}
        </sw-context-menu-item>
    </template>
</moorl-entity-grid-card-v2>
```

Der Slot erhält den Datensatz `item` und die Ladefunktion `reload`. Berechtigungen und Sperrzustände eigener Slot-Komponenten verwaltet der Aufrufer.

Die Eigenschaft `filters` nimmt ein Array von Shopware-Criteria-Filtern entgegen. Sie ergänzt die Filter aus `defaultItem`, ohne die Werte für das Anlegen neuer Datensätze zu verändern. Änderungen an `filters` oder `defaultItem` laden die erste Seite neu. So lassen sich etwa Bestell- oder Kundenlisten filtern, ohne die Grid-Komponente zu überschreiben.
