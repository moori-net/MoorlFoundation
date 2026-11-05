# Automatische Geocodierung per Queue Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Status:** Entwurf zur Durchsicht; keine Freigabe zur Implementierung. Die Voreinstellung der neuen Option ist ein Vorschlag.

**Goal:** Automatische Geocodierung optional außerhalb des Speichervorgangs ausführen und dabei aktuelle Adressen, Wiederholungen und parallele Worker korrekt behandeln.

**Architecture:** Der bestehende Subscriber erkennt relevante Änderungen. Im Queue-Modus versendet er kleine Nachrichten mit Entity-Name und ID; ein Handler liest den aktuellen Zustand und verwendet einen gemeinsamen Verarbeitungsservice. Die synchrone Länderzuordnung bleibt bestehen, während externe Geocodierung verzögert erfolgen kann.

**Tech Stack:** Shopware 6.7, Symfony Messenger, Doctrine DBAL, vorhandene Shopware Lock-/Cache-Infrastruktur, PHPUnit. Keine neue externe Abhängigkeit.

**Spec:** Entwurf im folgenden Abschnitt; noch nicht vom Nutzer bestätigt.

## Entwurf und Betriebsverhalten

- Vorgeschlagen: globale Plugin-Option `MoorlFoundation.config.autoLocationAsync`, Standard `false`. Bestehende Installationen verhalten sich ohne Aktivierung unverändert. Die Voreinstellung ist noch abzustimmen.
- Queue statt Scheduled Task: pro Änderung direkt einen Auftrag einstellen; keine periodische Suche nach geänderten Datensätzen und keine zusätzliche Wartungstabelle. Ein Scheduled Task wäre eine Alternative bei gewollter Stapelverarbeitung, bringt hier aber zusätzliche Zustandsverwaltung und Verzögerung.
- Bestehender Tag `moorl.foundation.entity_auto_location` bleibt die Schnittstelle für abhängige Plugins. Keine Anpassung von MoorlSignIn erforderlich. Betroffen sind unter anderem MoorlMerchantFinder, MoorlManufacturer, MoorlTicketVendor und AppflixCustomerMarket.
- Bei aktiviertem Queue-Modus sind neue Koordinaten erst nach Verarbeitung verfügbar. Vorhandene Koordinaten bleiben bis zu einem erfolgreichen Ergebnis bestehen. Ohne laufenden Consumer bleiben Aufträge liegen; kein stiller synchroner Rückfall.
- Nur automatische Adress-Geocodierung wird umgestellt. Interaktive Standortsuche, Auto-Cache und veraltete EntityLocationIndexer gehören nicht zum Umbau.
- Lokale Zuordnung von countryCode zu countryId bleibt im Speichervorgang, um bisherige Verbraucher nicht unnötig zu ändern.
- Nachrichten enthalten keine Adressen oder serialisierten Entities, sondern ausschließlich Entity-Name und ID. Der Handler akzeptiert nur registrierte Entity-Definitionen und gültige UUIDs.
- Ein Auftrag lädt den aktuellen Zustand. Gelöschte Datensätze und ausgeschaltete automatische Geocodierung werden übersprungen. Eine während der HTTP-Anfrage geänderte Adresse darf nicht mit alten Koordinaten überschrieben werden.
- Doppelte Zustellung darf keine falschen Ergebnisse erzeugen. Gemeinsame Sperre je Entity/ID und ein kurzlebiger Erfolgsvermerk mit Adressfingerprint vermeiden unmittelbar wiederholte API-Arbeit. Cacheverlust darf höchstens zusätzliche Arbeit verursachen.
- Die Drosselung muss für alle tatsächlichen API-Anfragen über LocationServiceV2 gelten, auch bei mehreren Workern und interaktiven Aufrufen. Eine gemeinsame Sperre und ein geteilter Zeitstempel ersetzen die rein lokale Wartezeit. Mindestabstand zwischen Request-Starts: eine Sekunde; keine Anfrage bei Cachetreffern. Mehrknotenbetrieb benötigt einen geteilten Store.
- Ein fehlender Treffer ist ein reguläres Ergebnis. Transportfehler, 429 und 5xx müssen für Queue-Aufrufe wiederholbar gemeldet werden; nach ausgeschöpften Versuchen greift der konfigurierte Failure-Transport. Die installierte Shopware-Konfiguration sieht standardmäßig drei Retries vor; Projektkonfiguration bleibt maßgeblich.
- Externe Anfragen und Sperren bekommen begrenzte Laufzeiten. Keine Datenbanktransaktion oder Zeilensperre über die HTTP-Anfrage hinweg halten.
- Keine allgemeine Neugestaltung des Geocoding-Caches aus Vorschlag 3. Aktuelle Zeitwerte im langlebigen Worker müssen jedoch je Aufruf neu ermittelt werden; das im Konstruktor gespeicherte `now` darf nicht dauerhaft altern.

## Global Constraints

- Standardbetrieb synchron erhalten, sofern die offene Entscheidung nicht anders ausfällt.
- Vorhandene öffentliche Standortmethoden und ihre bisherigen synchronen Fehlerkonventionen kompatibel halten.
- Keine neue externe Abhängigkeit und keine neue Datenbanktabelle vorsehen.
- SQL-Tabellennamen ausschließlich aus den registrierten Definitionen ableiten.
- Keine Produktionskonfiguration, Worker oder laufenden Dienste bei der Umsetzung ungefragt umstellen.

## Review Focus

- Adresse ändert sich während einer API-Anfrage: bedingtes Update schreibt keine veralteten Koordinaten.
- Derselbe Auftrag läuft parallel oder erneut nach einem Absturz: Ergebnis bleibt korrekt, keine verlorenen Änderungen.
- Entity wurde gelöscht oder autoLocation deaktiviert: kein API-Aufruf, kein Wiederanlegen.
- Mehrere Worker und interaktive Suche greifen gleichzeitig zu: gemeinsamer Mindestabstand bleibt erhalten.
- Queue-Aufruf erhält technischen Fehler statt fehlendem Treffer: Retry greift; synchrones Verhalten bleibt kompatibel.

## Task 1: Gemeinsame Verarbeitung und Schutz vor veralteten Ergebnissen

**Files:** Modify `src/Core/Service/EntityAutoLocationService.php`, `src/Resources/config/core/entity-auto-location.xml`; create `src/Core/Service/EntityAutoLocationProcessor.php`, `tests/Core/Service/EntityAutoLocationProcessorTest.php`.

**Interfaces:** `EntityAutoLocationProcessor::process(string $entityName, string $entityId, bool $retryTransportErrors = false): void`. Bestehende Länderzuordnung separat im Subscriber erhalten. Der Processor nutzt registrierte Definitionen, aktuelle Daten und LocationServiceV2.

- [ ] Tests zuerst schreiben: aktuelle Adresse wird verarbeitet; gelöschte Entity, deaktivierte Automatik und unbekannter Entity-Name lösen keine Geocodierung aus; während der Anfrage geänderte Adresse wird nicht überschrieben; auch auf null gesetzte relevante Adressfelder gelten als Änderung.
- [ ] `php vendor/bin/phpunit custom/plugins/MoorlFoundation/tests/Core/Service/EntityAutoLocationProcessorTest.php` ausführen und die erwarteten Fehlschläge prüfen.
- [ ] Verarbeitung aus `handle()` herauslösen; relevante Write-Payload-Felder anhand realer DAL-WriteResults inklusive null-Werten erkennen. Länderzuordnung vor der Geocodierung ausführen. UUID-Binärparameter korrekt binden.
- [ ] Koordinaten per bedingtem SQL-Update schreiben: ID, autoLocation und alle geocodierten Adresswerte müssen weiterhin zum gelesenen Snapshot passen. Bei zwischenzeitlicher Änderung nichts schreiben; die neue Änderung erzeugt ihren eigenen Auftrag.
- [ ] Tests erneut ausführen. Synchrone Verhaltensgleichheit einschließlich Länderzuordnung prüfen. Zusammenhängenden Änderungsschritt reviewen.

## Task 2: API-Verhalten für parallele, langlebige Worker absichern

**Files:** Modify `src/Core/Service/LocationServiceV2.php`, `src/Resources/config/core/location.xml`; create `src/Core/Service/GeocodingRequestGate.php`, `tests/Core/Service/LocationServiceV2Test.php`, `tests/Core/Service/GeocodingRequestGateTest.php`.

**Interfaces:** `GeocodingRequestGate::execute(callable $request): mixed` kapselt gemeinsame Drosselung und Sperre. `getLocationByAddress()` erhält einen abschließenden optionalen Parameter `bool $retryTransportErrors = false`; rekursive Suchversuche reichen ihn weiter.

- [ ] Tests zuerst schreiben: zwei parallele Aufrufe halten den gemeinsamen Mindestabstand ein; Cachetreffer benötigen keinen API-Slot; technische Fehler werden im Retry-Modus weitergereicht; fehlende Treffer liefern null; alte Aufrufer behalten die Fehlerkonvention; ein lange lebender Service verwendet aktuelle Zeitwerte.
- [ ] Neue Tests ausführen und erwartete Fehlschläge prüfen.
- [ ] Geteilten Sperr-/Zeitstempel-Store aus der vorhandenen Infrastruktur anbinden. Sperren über Exceptions hinweg zuverlässig freigeben, TTL und Erneuerung an die maximale Request-Laufzeit anpassen. Wartende Queue-Aufträge zeitversetzt wiederholen, statt einen Worker unbegrenzt blockieren zu lassen.
- [ ] Retry-Modus ergänzen, transiente API-Fehler erhalten und die Zeitberechnung je Operation erneuern. HTTP-Zeitlimit für Queue-Aufrufe auf 15 Sekunden pro Versuch begrenzen; synchrones Zeitlimit zunächst unverändert lassen. Alle API-Versuche passieren denselben Request-Gate.
- [ ] Neue Tests erneut ausführen; Parallelität zusätzlich mit zwei Prozessen gegen denselben Store prüfen. Zusammenhängenden Änderungsschritt reviewen.

## Task 3: Nachricht, Handler und optionale Umschaltung

**Files:** Create `src/Core/Message/AutoGeocodeMessage.php`, `src/Core/Message/AutoGeocodeMessageHandler.php`, `tests/Core/Message/AutoGeocodeMessageHandlerTest.php`, `tests/Core/Service/EntityAutoLocationServiceTest.php`; modify `src/Core/Service/EntityAutoLocationService.php`, `src/Resources/config/core/entity-auto-location.xml`, `src/Resources/config/config.xml`.

**Interfaces:** `AutoGeocodeMessage::__construct(public readonly string $entityName, public readonly string $entityId)` implements Shopwares `AsyncMessageInterface`; `AutoGeocodeMessageHandler::__invoke(AutoGeocodeMessage $message): void` ruft den Processor mit Retry-Modus auf. Die installierte Shopware-Konfiguration routet AsyncMessageInterface auf `async`.

- [ ] Tests zuerst schreiben: Standardmodus verarbeitet synchron; Queue-Modus dispatcht je eindeutiger relevanter ID einen Auftrag und führt keine externe Anfrage aus; unrelevante Änderungen und leere ID-Mengen dispatchen nichts; Nachricht lässt sich mit dem tatsächlichen Messenger-Serializer hin und zurück serialisieren.
- [ ] Handler-Tests ergänzen: doppelte Zustellung, konkurrierende Verarbeitung, Sperrkonflikt mit verzögerter Wiederholung, geänderte Adresse nach vorigem Erfolg, geänderter autoLocation-Wert, gelöschte Entity und transienter Fehler. Erfolg erst nach erfolgreichem bedingtem Update vermerken.
- [ ] Neue Tests ausführen und erwartete Fehlschläge prüfen.
- [ ] Globale Option ergänzen; MessageBus und Handler registrieren. Geteilte Sperre je Entity/ID und Erfolgsvermerk mit Adressfingerprint und Koordinaten für 300 Sekunden verwenden. Kein Dispatch-Deduplizieren, das Adressänderungen oder Retries verlieren könnte.
- [ ] Integrationsprüfung: Zeitpunkt der Event-Auslösung und Commit-Grenzen im tatsächlichen Shopware-Schreibpfad prüfen; eine zurückgerollte Änderung darf keine falschen Koordinaten schreiben. Ein unmittelbar gestarteter Worker muss den committed Zustand sehen. Bei Rollback-/Visibility-Problemen Dispatch nach Commit anbinden, bevor der Queue-Modus freigegeben wird.
- [ ] Neue Tests erneut ausführen und Servicecontainer/Message-Routing prüfen. Zusammenhängenden Änderungsschritt reviewen.

## Task 4: Betriebsprüfung und Dokumentation

**Files:** Create `docs/auto-geocoding.md`, `docs/auto-geocoding.en.md`; ergänzende Integrationstests für die Queue-Anbindung.

- [ ] Dokumentieren: Option und Voreinstellung, verzögerte Koordinaten, bestehende Werte während der Verarbeitung, gemeinsam genutzter Lock-/Cache-Store, laufender Consumer für `async`, Retry-/Failure-Verhalten. Aktivierung erst nach Einrichtung des Consumers.
- [ ] Kontrollierten Import mit 100 Datensätzen und Fake-Geocoding-Endpunkt vergleichen: Speicherdauer, API-Aufrufe während des Requests, Queue-Laufzeit, Anzahl geschriebener Ergebnisse. Ziel im Queue-Modus: null externe Geocoding-Aufrufe im Speichervorgang, korrekte spätere Koordinaten.
- [ ] Paralleltest mit zwei Workern; Adresse während laufender Anfrage ändern; autoLocation deaktivieren; Datensatz löschen; API-Timeout/429/5xx und reguläres leeres Ergebnis auslösen. Erwartung: keine veralteten Updates, Wiederholung nur bei technischen Fehlern, gemeinsame Drosselung eingehalten.
- [ ] `php vendor/bin/phpunit custom/plugins/MoorlFoundation/tests` ausführen. Bereits bekannter unabhängiger Fehler: `ClientChatGptTest::testProvidesOpenAiModelAndReasoningConfiguration` erwartet deutsche statt englischer Ja/Nein-Labels. Neue Fehler separat bewerten.
- [ ] `git -C custom/plugins/MoorlFoundation diff --check` und abschließende Code-Durchsicht ausführen; Ergebnisse und verbleibende Betriebsanforderungen berichten.

## Offene Entscheidung

Soll `autoLocationAsync` optional und standardmäßig deaktiviert sein (Vorschlag), oder wird Queue-Verarbeitung der neue Standard? Umsetzung erst nach Durchsicht dieses Entwurfs.
