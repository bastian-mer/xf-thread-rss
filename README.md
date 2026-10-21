# Thread RSS Feeds für XenForo 2.3

Add-on für XenForo 2.3, das für **einzelne Themen** einen RSS-Feed bereitstellt. XenForo selbst bietet Feeds nur für ganze Foren an (`/forums/…/index.rss`). Dieses Add-on ergänzt dasselbe Schema für Threads.

- **Add-on-ID:** `Alpinforum/ThreadRss`
- **Version:** 1.0.0

## Funktionen

- Feed pro Thema unter `/threads/themenname.123/index.rss`
- Enthält die 25 neuesten sichtbaren Beiträge, neueste zuerst
- Jeder Eintrag mit Autor, Datum, Kurztext und vollständig gerendertem Beitrag (`content:encoded`)
- Stabile Links und GUIDs über die Beitrags-URL (`/posts/123`), damit Einträge beim Umbenennen eines Themas nicht doppelt erscheinen
- Button „RSS-Feed dieses Themas“ unter jedem Thema
- Autodiscovery-Link im Seitenkopf, damit Browser und Feed-Reader den Feed automatisch finden
- Berechtigungen werden wie beim normalen Aufruf des Themas geprüft

## Voraussetzungen

- XenForo 2.3.0 oder neuer
- PHP 7.2 oder neuer

## Installation

**Aus Archiv (empfohlen):**

1. ZIP-Archiv erstellen, das den Ordner `upload/` enthält (siehe unten), oder ein fertiges Release herunterladen.
2. Im ACP: *Add-ons → Aus Archiv installieren/aktualisieren* → ZIP hochladen.

**Manuell:**

1. Den Inhalt von `upload/` in das Wurzelverzeichnis der XenForo-Installation kopieren.
2. Im ACP unter *Add-ons* bei „Thread RSS Feeds“ auf *Installieren* klicken.

ZIP aus dem Repository erzeugen:

```bash
zip -r Alpinforum-ThreadRss-1.0.0.zip upload
```

## Verwendung

An die URL eines Themas einfach `index.rss` anhängen:

```
https://example.com/threads/mein-thema.123/index.rss
```

Alternativ den RSS-Button unter dem Thema verwenden.

**Hinweis zu Berechtigungen:** Feed-Reader rufen den Feed in der Regel als Gast ab. Themen in Foren, die Gäste nicht sehen dürfen, liefern daher eine Fehlermeldung statt Inhalten.

## Anpassen

- **Texte:** ACP → *Darstellung → Phrasen*, Suche nach `alpinforum_threadrss`
- **Anzahl der Beiträge:** Konstante `ALPINFORUM_THREADRSS_LIMIT` in `upload/src/addons/Alpinforum/ThreadRss/XF/Pub/Controller/Thread.php` (Standard: 25)
- **Button/Head-Link:** Template-Modifikation `alpinforum_threadrss_thread_view` (Template `thread_view`), im ACP unter *Darstellung → Template-Modifikationen* deaktivierbar

## Aufbau

```
upload/src/addons/Alpinforum/ThreadRss/
├── addon.json
├── Setup.php
├── XF/Pub/Controller/Thread.php   Erweiterung des Thread-Controllers (index.rss)
├── Pub/View/Thread/Rss.php        Erzeugt das RSS-2.0-XML
└── _data/                         Klassen-Erweiterung, Phrasen, Template-Modifikation
```

## Deinstallation

Im ACP unter *Add-ons* deinstallieren. Anschließend kann der Ordner `src/addons/Alpinforum/ThreadRss` gelöscht werden.

## Lizenz

MIT, siehe [LICENSE](LICENSE).
