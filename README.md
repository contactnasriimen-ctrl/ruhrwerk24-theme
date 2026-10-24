# Ruhrwerk24 – WordPress-Theme

Theme für einen Handwerks- und Notdienstbetrieb in NRW: Rohrreinigung, Haustechnik, Ausbau. Mit 3D-Rohrnetz im Seitenkopf, mehrstufigem Anfrageformular und automatischen Stadt-Landingpages.

**Live-Demo:** https://contactnasriimen-ctrl.github.io/ruhrwerk24-theme/
(statische Kopie – Kontaktdaten sind Platzhalter, das Formular sendet nichts)

**Download:** installierbares ZIP unter [Releases](https://github.com/contactnasriimen-ctrl/ruhrwerk24-theme/releases/latest)

![Startseite](ruhrwerk24/screenshot.png)

## Funktionen

- **3D-Seitenkopf** (Three.js, lokal eingebunden): Metallrohre mit Flanschen, die beim Laden „wachsen“, orangefarbene Strömung, schwebende Muttern; reagiert auf Maus und Scrollen. Wird erst nach dem Seitenaufbau geladen, pausiert außerhalb des Sichtbereichs und entfällt auf schwachen Geräten, bei Datensparmodus und bei „Bewegung reduzieren“.
- **Weitere 3D-Szenen:** Absperrventil, dessen Handrad sich beim Scrollen dreht (Abschluss-Band), undichte Muffe mit fallenden Tropfen und Blaulicht (Notdienst-Band), leichtes Rohrnetz in jedem Seitenkopf. Nebenszenen nur auf großen Bildschirmen.
- **3D-Hintergrund der ganzen Seite:** feste 3D-Ebene hinter allen hellen Abschnitten – schwebende Muttern, Scheiben, Rohrbögen und Rohrstücke, lange Leitungen mit Strömung, Lichtstaub. Beim Scrollen fliegt die Kamera durch den Raum, schnelles Scrollen dreht die Teile schneller. Pausiert, wenn nur dunkle Abschnitte zu sehen sind.
- **3D-Oberfläche:** Karten kippen zur Maus mit Lichtreflex, Überschriften bauen sich Wort für Wort auf, Abschnitte klappen beim Scrollen ins Bild, plastische 3D-Ziffern, 3D-Karussell der Leistungsbereiche (Pfeile, Wischen, Autoplay), Knöpfe mit Tiefe und Magnet-Effekt, leuchtender Rahmen um das Formular, Laufband, Lesefortschritt.
- **Footer (seit 2.6):** 3D-Netzkarte NRW (alle Städte an ihrer echten Position, Lichtbögen vom Firmensitz, wandernde Impulse, Radarring), „Notdienst jetzt erreichbar“ mit Aufruf, Lichtkante, riesiger Schriftzug mit Glanzlicht, „Nach oben“ mit Fortschrittsring. Städte tragen Koordinaten, die auch ins Schema.org-Markup gehen.
- **Responsiv geprüft** auf 10 Größen von 320 px bis 2560 px inklusive Tablet und Handy quer: kein seitliches Scrollen, Tippflächen ab 44 px. Auf Handy und Tablet bekommt die 3D-Szene eine eigene Bühne zwischen Text und Formular; Ventil und Tropfen laufen auch dort und starten erst beim Heranscrollen.
- **Umzug & Transport (seit 2.5):** Umzug, Firmenumzug, Transporte & Möbellieferung – mit 3D-Transporter (Markenaufdruck, nächtliche Stadt, drehende Räder, schwebende Umzugskartons, Funkenspur; Scrollen gibt Gas) im Startseiten-Band, neben dem Text der Transport-Leistungen und im Seitenkopf auf dem Handy. Links wie `/anfrage/?leistung=umzug` wählen die Leistung im Formular vor.
- **Startseite:** Laufband, Bento-Raster „Warum Ruhrwerk24“, Zahlenband, Über uns mit 3D-Karussell (Text im Customizer), Ablauf mit Zeitleiste, Notdienst, Einsatzgebiete, allgemeine FAQ.
- **Inhalte:** ausführliche Texte zu allen acht Leistungen; jede Stadtseite mit eigenem Absatz zur Region (Ruhrgebiet, Rheinland, Bergisches Land, Niederrhein, Münsterland). Theme-Updates frischen nur Texte auf, die niemand bearbeitet hat.
- **Anfrageformular in acht Schritten** ohne Plugin und ohne Captcha (Honeypot, signierter Zeitstempel, Limit pro IP). Funktioniert auch ohne JavaScript.
- **Anfragen im Backend:** Status, interne Notiz, CSV-Export, automatische Löschung nach X Monaten (DSGVO), Bestätigungsmail mit Anfragenummer.
- **Stadtseiten ohne Datenbankeinträge:** `/rohrreinigung-essen/` und `/servicegebiete/essen/` für 27 Städte, mit eigenen Inhalten je Stadt und in der XML-Sitemap.
- **SEO:** Meta-Description, Open Graph, Canonical, Schema.org (LocalBusiness, Service, FAQ, Breadcrumbs). Zieht sich bei Yoast, Rank Math oder SEOPress zurück.
- **Datenschutz:** keine externen Anfragen, keine Cookies, kein jQuery, Schrift lokal.
- **Einrichtung per Klick:** Beim Aktivieren entstehen Leistungen, Seiten und Menüs.

### Neu in 2.7

- **Umzug & Transport zuerst** – auf der Startseite, in Menüs, Formular und überall, wo Leistungen gelistet sind.
- **Orange statt Rot** für Notdienst-Elemente (`#C2410C`, AA-Kontrast mit weißer Schrift).
- **Impressum und Datenschutzerklärung** werden aus den Customizer-Angaben erzeugt (Shortcodes `[rw24_impressum]`, `[rw24_datenschutz]`). Vorlage – bitte rechtlich prüfen lassen.
- **Cookie-Hinweis** mit gleichwertigem „Ablehnen“ und „Akzeptieren“, Einstellungen je Kategorie, jederzeit änderbar. Optionaler Statistik-Code läuft erst nach Zustimmung.
- **Hashtags & Teilen** auf Leistungs- und Stadtseiten: Hashtags als interne Links, Teilen-Knöpfe ohne fremde Scripts.
- **Schneller**: gebündelte und minifizierte Assets in `ruhrwerk24/assets/dist/` (kritisches CSS+JS 29 → 20 KB gzip, 4 → 2 Anfragen; inkl. 3D 216 → 168 KB gzip).

## Installation

1. ZIP aus den [Releases](https://github.com/contactnasriimen-ctrl/ruhrwerk24-theme/releases/latest) unter *Design › Themes › Theme hinzufügen* hochladen und aktivieren.
2. *Design › Customizer › Ruhrwerk24*: Kontaktdaten, Farben, Texte eintragen.
3. Impressum und Datenschutzerklärung ausfüllen, Fotos als Beitragsbilder der Leistungen hinterlegen.
4. Für zuverlässigen Mailversand ein SMTP-Plugin einrichten.

Voraussetzungen: WordPress 6.4+, PHP 8.0+.

## Aufbau

```
ruhrwerk24/          das Theme
  inc/               Logik (Inhaltstypen, Formular, Anfragen, Städte, SEO, Sitemap)
  template-parts/    wiederverwendbare Bausteine
  templates/         Stadtseiten
  assets/js/hero3d.js  3D-Szene
docs/                statische Demo für GitHub Pages
scripts/
  build-assets.mjs   minifizierte Assets nach ruhrwerk24/assets/dist/ (esbuild)
  build-zip.php      installierbares ZIP nach dist/
  build-demo.php     Demo aus einer lokalen WordPress-Installation nach docs/
```

Demo neu bauen (lokales WordPress mit aktivem Theme vorausgesetzt):

```
php scripts/build-demo.php http://localhost:8124 https://contactnasriimen-ctrl.github.io/ruhrwerk24-theme https://github.com/contactnasriimen-ctrl/ruhrwerk24-theme
```

## Lizenzen

Theme: GPL-2.0-or-later · Three.js: MIT · Schrift Archivo: SIL Open Font License 1.1

Entwickelt von [Over-Tech.de](https://over-tech.de).
