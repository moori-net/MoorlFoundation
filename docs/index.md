# Foundation | Basis Version blablalvla

## Foundation wird von folgenden Plugins verwendet

Sofern nicht anders angegeben, wird moori Foundation in folgenden Plugins verwendet:

- Plugins von [Appflix](https://store.shopware.com/appflix-ug.html)
- Plugins von [moori](https://store.shopware.com/moori.html)

## Fehlende Dienste nachladen

Einige Dienste benötigen composer Pakete, folgende Befehle kannst du im Hauptverzeichnis deines Shopware 6 Shops ausführen:

```text
composer require deeplcom/deepl-php
```

## Wozu dient Foundation?

Das Portfolio von Appflix und moori umfasst mittlerweile um die 100 Shopware 6 Plugins. Dabei treten oft immer wiederkehrende Problemstellungen auf.

Deshalb sind gemeinsam genutzte Funktionen im Foundation zusammen gefasst und dienen als Ausgleich für fehlende Funktionen im Shopware System.

Einige Beispiele:

- FontAwesome SVG: Liefert weitere Icons für die Gestaltung der Storefront-Seiten.
- Animate CSS: Wird für Animationen im Storefront genutzt.
- OpenStreetMap und Karten Marker: Dient als Basis für Store Locator, DeliveryWare (Appflix) und Kleinanzeigen (Appflix).
- [Listings, Slider und Sortierungen](listing.md): Dient allen Plugins, die eigene Entitäten haben. Für eine schnelle Integration in den Shopware 6 Core. Die Basis unterstützt auch [alle Suchfunktionen](advanced-search.md) in der Storefront.
- Automatische Übersetzungen: Mithilfe von DeepL können sämtliche Sprachbezogene Inhalte übersetzt werden.
- [Demo Assistent](demo-assistant.md): Ermöglicht es, Demo-Inhalte im JSON Format zu erstellen und in Shopware 6 zu importieren. Dieses Feature wird auch in den Themes von RH Webdesign eingesetzt.
- CMS Tools: Eine sehr umfangreiche Sammlung von nützlichen Tools.
- CMS Elemente: Alle CMS Elemente, die für mehrere Plugins geeignet sind, wurden in Foundation zusammen gefasst.
- usw...

Du kannst gerne das Foundation als Basis für deine Plugins oder Projekte nutzen. Es ist jedoch untersagt Features oder Tools aus Foundation für eigene Zwecke zu entnehmen.

## Premium Features

Einige Funktionen können durch ein kostenpflichtiges Plugin freigeschaltet werden. Das gilt speziell für die CMS Tools und der Übersetzungsfunktion mit DeepL.

[Zum Plugin](https://store.shopware.com/moorl87443379024/features-add-on-foundation.html)

[Mehr Infos](features-premium.md)

## Free Features

Diese dienen in erster Line dazu, um die Appflix und moori Plugins schlang zu halten, können aber auch frei für weitere Zwecke genutzt werden.

[Mehr Infos](features-free.md)

## Einstellungen

### Open Street Map

Da einige Plugins von Appflix und moori diesen Dienst nutzen, wurde diese nun im Foundation eingebaut. Es wurde sich hier um eine Alternative zu Google Maps entschieden, da OpenStreetMap im nichts nachsteht. Im Gegenteil: Es hat durchaus viele Vorteile.

Es gibt ein CMS Element für die Karte, welches genutzt werden kann. Alle weiteren Optionen dienen jedoch für die Plugins.

![](images/foundation-open-street-map-01.jpg)

- Url für Kachel-Ebene: Du kannst die Basis URL nutzen, aber auch z.B. eine persönliche Mapbox URL. Die Kacheln kannst du ganz nach deinen Vorstellungen gestaltet. [Hier findest du Beispiele](https://leaflet-extras.github.io/leaflet-providers/preview/)
- Copyright: Es ist wichtig, dass Du den Copyright Hinweis immer angibst. Diese unterscheidet sich je nach dem Anbieter, den du für deine Kachel-Ebenen nutzt.
- Weitere Einstellungen: Hier kannst du explizit angeben, wie sich die Karte bei einer Interaktion verhält.
- Länder: Für die Ermittlung von Geo-Koordinaten kannst du hier die Suche auf Länder begrenzen.
- Einheit: Für die Messung der Entfernung kannst du das Ergebnis in Meilen oder Kilometer angeben.

### Map Marker

Du kannst deine Orte mit individuellen Markern kennzeichnen. Nutze den [Demo Assistenten](demo-assistant.md) für voreingestellte Marker.

Unter Einstellungen, Erweiterungen, moori Map marker findest du die Konfiguration.

![](images/foundation-map-marker-01.jpg)

Du kannst eigene SVGs als HTML nutzen oder auch Grafiken. Die Grafiken bestehen aus drei Ebenen: Marker, Retina und Schatten.

![](images/foundation-map-marker-02.jpg)

![](images/foundation-map-marker-03.jpg)

Zu den Einstellungen hilft dir die [Offizielle Dokumentation](https://leafletjs.com/examples/custom-icons/) von Leaflet weiter.

![](images/foundation-map-marker-04.jpg)

Du erhältst in der Vorschau sofort Feedback, ob dein Marker richtig konfiguriert wurde.

![](images/foundation-map-marker-05.jpg)

### Import und Export

![](images/foundation-import-export-01.jpg)

![](images/foundation-import-export-02.jpg)
