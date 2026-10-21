# Social Links

Das CMS-Element **Social Links** zeigt eine nummerierte Linkliste mit Beschriftung, Benutzername und einem Pfeil. Die vollständige Zeile öffnet die konfigurierte URL in einem neuen Tab. Optional lassen sich eine Überzeile und ein Titel eintragen.

Jeder Link besitzt genau drei Eingabefelder:

- `label`: sichtbare Beschriftung, zum Beispiel Instagram.
- `username`: optionaler Benutzername oder Zusatztext, zum Beispiel @artist.
- `url`: vollständige HTTP- oder HTTPS-Adresse.

Einträge mit den Pfeilen nach oben oder unten verschieben. Die Reihenfolge wird direkt im Array gespeichert; ein Positionsfeld ist nicht erforderlich. Alle Einträge können entfernt werden. Ohne gültige Links wird das CMS-Element nicht ausgegeben.

Der Foundation-Formular-Builder verwendet dieselbe Bearbeitung automatisch für Entity-Felder mit dem Namen `socialLinks`, im Reiter `general` und Abschnitt `contact`. Das Feldset liegt unter `fieldsetsConfig['social-links']`. Die Artist-Verwaltung in Ticket Vendor verwendet diese Zuordnung ebenfalls.
