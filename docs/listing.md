# Foundation | Listings und Slider

verfügbar ab Shopware 6.4

Alle moori Plugins mit Listings erhalten ein CMS mit diesen oder ähnlichen Einstellungen.

_Hinweis: Falls du das Element nicht im Admin findest, 
beachte folgende  [Hinweise](../)_

## Was ist das Listing Element

Das Listing Element ist ein CMS Element mit mehreren Funktionen.
Es können Listings mit Pagination, statische Listings und Listings mit einzeln ausgewählten
Elementen geben.

Durch den Fremdschlüssel können auch spezifische Listings anhand der aktuell dargestellten
Seite im Storefront erstellt werden. In diesem Beispiel bezieht sich der Fremdschlüssel auf
die Unterkategorien einer angezeigten Kategorie.

![](images/listing-01.jpg)

## Die Einstellungen

### Listing Quelle

![](images/listing-02.jpg)

Die Quelle bestimmt woher die Elemente des Listing geladen werden.

- Dynamisch: Nicht alle Elemente haben dynamische Listings, ein dymanisches
  Listing kann man z.B. mit einem Produklisting vergleichen. Diese entählt eine Pagination
  und es lassen sich Suchfilter nutzen.
  
- Statisch: Statische Listings orientieren sich nach der Sortierung. Damit kann man z.B. 
  im Blog Plugin ein statisches Listing mit der Sortierung "Neue Beiträge" erstellen.
  
- Auswählen: Es können auch einzeln Elemente für das Listing ausgewählt werden.

![](images/listing-03.jpg)

### Fremdschlüssel

![](images/listing-04.jpg)

Durch den Fremdschlüssel lassen sich die Elemente filtern. Dies ist eine Alternative
zum Mapping von CMS Feldern.

### Listing Layout

![](images/listing-05.jpg)

Es gibt unterschiedliche Layouts. Das Listing wird entsprechend der Auswahl dargestellt.

### Element Layout

![](images/listing-06.jpg)

Auch das Element selbst kann ein gewünschtes Layout haben. Hier kann man außerdem auch 
ein eigenes Twig Template einbinden.

![](images/listing-07.jpg)

### Slider Modus

Sofern das Listing Layout "Slider" ausgewählt ist, kannst du diesen nochmals detailliert
konfigurieren.

Es gibt zwei unterschiedliche Modi für den Slider.

![](images/listing-08.jpg)

Wenn man den Modus "Galerie" auswählt, kann man zusätzlich Übergangsanimationen einstellen.

![](images/listing-09.jpg)

### Element Einstellungen

Hier lassen sich generelle Einstellungen am Element vornehmen.

![](images/listing-10.jpg)

### Inhalt Einstellungen

Der Call-To-Action Bereich beinhaltet ebenfalls diverse Einstellungen. So bleiben keine Wünsche offen!

![](images/listing-11.jpg)
