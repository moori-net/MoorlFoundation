## Über die Erweiterung

Dieses Plugin bildet die Grundlage für zahlreiche Erweiterungen. Es beinhaltet Animationen, **Font Awesome 5 Free** mit über 1.500 Icons sowie wiederkehrende Funktionen und Übersetzungen.

## Verwendung von Foundation

Sofern nicht anders angegeben, wird **moori Foundation** in folgenden Plugins eingesetzt:

* Plugins von [Appflix](https://store.shopware.com/de/extension-partners/appflix-ug)
* Plugins von [moori](https://store.shopware.com/de/extension-partners/moori)

## Zweck von Foundation

Das Portfolio von Appflix und moori umfasst mittlerweile über 100 Plugins für Shopware 6. Dabei entstehen naturgemäß verschiedenste Herausforderungen an ganz unterschiedlichen Stellen.

Um die Plugins schlank zu halten und den Shopware Core sinnvoll zu erweitern, wurden zahlreiche Kernfunktionalitäten in Foundation gebündelt.

**Beispiele für Funktionen:**

- *FontAwesome SVG:* Zusätzliche Icons für die Gestaltung Ihrer Storefront-Seiten
- *Animate CSS:* Animationen für die Storefront
- *OpenStreetMap und Kartenmarker:* Grundlage für Store Locator, DeliveryWare (Appflix) und Kleinanzeigen (Appflix)
- *Listings, Slider und Sortierungen:* Basis für alle Plugins, die eigene Entitäten nutzen
- *Automatische Übersetzungen:* Mithilfe von DeepL können sämtliche sprachbezogenen Inhalte übersetzt werden
- *Demo-Assistent:* Erstellung und Import von Demo-Inhalten im JSON-Format (u. a. in den Themes von RH Webdesign im Einsatz)
- *CMS Tools:* Eine umfangreiche Sammlung nützlicher Werkzeuge
- *CMS Elemente:* Alle relevanten CMS-Elemente für verschiedene Plugins sind in Foundation zusammengeführt
- und vieles mehr …

Sie können Foundation auch als Grundlage für Ihre eigenen Plugins oder Projekte verwenden.  
Es ist jedoch nicht gestattet, einzelne Features oder Tools daraus für eigene Zwecke zu entnehmen.

## Premium Features

Einige Funktionen lassen sich durch ein kostenpflichtiges Upgrade freischalten.  
Dies betrifft insbesondere die CMS Tools sowie die Übersetzungsfunktion via DeepL.

[Zum Plugin](https://store.shopware.com/de/moorl87443379024m12/features-add-on-foundation.html)

## Free Features

Die kostenlosen Funktionen dienen in erster Linie dazu, die Plugins von Appflix und moori schlank zu halten.  
Natürlich können diese auch frei für weitere Zwecke eingesetzt werden.

## Einstellungen

### OpenStreetMap

Da einige Plugins von Appflix und moori OpenStreetMap nutzen, ist diese Technologie direkt in Foundation integriert.  
Als Alternative zu Google Maps bietet OpenStreetMap zahlreiche Vorteile, ohne funktionale Abstriche.

Ein CMS-Element für Karten steht Ihnen frei zur Verfügung. Weitere Optionen werden von spezifischen Plugins des Portfolios genutzt.

- *URL für Kachel-Ebene:* Sie können die Standard-URL nutzen oder eine persönliche Mapbox-URL. Damit lassen sich Karten individuell gestalten. [Beispiele finden Sie hier](https://leaflet-extras.github.io/leaflet-providers/preview/).
- *Copyright:* Bitte beachten Sie, dass stets ein Copyright-Hinweis erforderlich ist. Dieser variiert je nach Anbieter.
- *Weitere Einstellungen:* Definieren Sie, wie die Karte auf Interaktionen reagieren soll.
- *Ländereinschränkung:* Begrenzen Sie die Suche auf bestimmte Länder, um Geo-Koordinaten gezielt zu ermitteln.
- *Einheit:* Entfernungen können in Meilen oder Kilometern angegeben werden.

### Map Marker

Sie können Standorte mit individuellen Markern kennzeichnen.  
Nutzen Sie hierfür den **Demo-Assistenten**, um voreingestellte Marker zu laden.

- Unter *Einstellungen → Erweiterungen → moori Map Marker* finden Sie die Konfigurationsmöglichkeiten.

Eigene Marker lassen sich entweder als SVG-HTML oder als Grafiken einbinden. Die Grafikstruktur umfasst drei Ebenen: Marker, Retina und Schatten.

Die [offizielle Dokumentation von Leaflet](https://leafletjs.com/examples/custom-icons/) unterstützt Sie bei der Konfiguration.

Sobald Ihr Marker vollständig eingerichtet ist, erhalten Sie direkt eine Vorschau mit sofortigem Feedback.  
