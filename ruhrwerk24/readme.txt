=== Ruhrwerk24 ===
Contributors: over-tech
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.0
License: GPL-2.0-or-later

Theme für Ruhrwerk24 – Handwerk, Sanierung und Notdienst in NRW.

== Installation ==

1. ZIP unter Design > Themes > Theme hinzufügen hochladen und aktivieren.
2. Beim Aktivieren legt das Theme automatisch an: 3 Leistungsgruppen,
   8 Leistungen, die Seiten Startseite, Anfrage, Notdienst, Servicegebiete,
   Impressum, Datenschutz sowie Haupt- und Rechtsmenü. Bestehende Inhalte
   werden nie überschrieben.
3. Design > Customizer > Ruhrwerk24: Kontaktdaten, Farben, Startseite,
   Notdienst-Texte und Löschfrist für Anfragen eintragen.
4. Impressum und Datenschutzerklärung mit echten Angaben füllen.
5. Fotos: je Leistung ein Beitragsbild (ideal 1920×1080), Startseite über
   den Customizer.

== Was das Theme mitbringt ==

* 3D-Seitenkopf (Three.js, lokal): Rohrnetz mit Strömung, reagiert auf
  Maus und Scrollen. Lädt nach dem Seitenaufbau, entfällt auf schwachen
  Geräten und bei "Bewegung reduzieren". Erscheint auf der Startseite
  ohne Hintergrundfoto und auf Leistungsseiten ohne Beitragsbild.
* 3D-Oberfläche: kippende Karten mit Lichtreflex, Überschriften Wort für
  Wort, Einblenden in 3D, Knöpfe mit Tiefe.
* Anfrageformular mit acht Schritten, ohne Plugin, ohne Captcha
  (Honeypot, signierter Zeitstempel, Limit pro IP). Funktioniert auch
  ohne JavaScript.
* Anfragen im Backend unter "Anfragen": Status, Notiz, CSV-Export,
  automatische Löschung nach X Monaten (DSGVO).
* Bestätigungsmail an den Kunden mit Anfragenummer (RW-JJMM-0001).
  Für zuverlässigen Versand ein SMTP-Plugin einrichten.
* Stadtseiten ohne Datenbankeinträge: /rohrreinigung-essen/ und
  /servicegebiete/essen/ für 27 Städte. Liste in inc/cities.php oder per
  Filter "rw24_cities".
* SEO: Meta-Description, Open Graph, Canonical, Schema.org
  (LocalBusiness, Service, FAQ, Breadcrumbs), Stadtseiten in der
  XML-Sitemap. Bei aktivem Yoast, Rank Math oder SEOPress zieht sich das
  Theme bei Description/OG zurück.
* Keine externen Anfragen: Schrift lokal, keine Cookies, kein jQuery.

== Filter für Entwickler ==

rw24_cities, rw24_usps, rw24_credit, rw24_meta_description,
rw24_schema_graph, rw24_breadcrumb_trail, rw24_service_image,
rw24_lead_rate_limit. Aktion rw24_lead_created für CRM-Anbindungen.

== Lizenzen ==

Archivo (assets/fonts/archivo.woff2): SIL Open Font License 1.1.
Three.js r170 (assets/vendor/three): MIT.
