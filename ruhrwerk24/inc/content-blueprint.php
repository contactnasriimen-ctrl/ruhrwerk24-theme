<?php
/**
 * Inhalts-Bauplan: Leistungsgruppen und Leistungen.
 *
 * Wird beim Aktivieren des Themes einmalig angelegt (setup-content.php).
 * Danach gehören die Texte der Redaktion - Änderungen hier wirken nur auf
 * Einträge, die es noch nicht gibt.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Leistungsgruppen in Anzeigereihenfolge.
 *
 * @return array<string, array{name: string, description: string}>
 */
function rw24_blueprint_groups(): array {
	return array(
		'kanaltechnik' => array(
			'name'        => __( 'Kanaltechnik', 'ruhrwerk24' ),
			'description' => __( 'Verstopfung, Wurzeleinwuchs, undichte Leitungen: Wir finden die Ursache mit der Kamera und beheben sie – meist ohne aufzugraben.', 'ruhrwerk24' ),
		),
		'haustechnik'  => array(
			'name'        => __( 'Haustechnik', 'ruhrwerk24' ),
			'description' => __( 'Wasser, Wärme, Strom. Installation, Wartung und Reparatur durch eigene Meister – inklusive Notdienst rund um die Uhr.', 'ruhrwerk24' ),
		),
		'ausbau'       => array(
			'name'        => __( 'Ausbau & Räumung', 'ruhrwerk24' ),
			'description' => __( 'Vom Bad bis zum Keller: Wir bauen aus, renovieren und räumen – sauber, termintreu und zum Festpreis.', 'ruhrwerk24' ),
		),
	);
}

/**
 * Die acht Leistungen.
 *
 * Felder: title, group, icon, claim, excerpt, emergency, content (HTML),
 * features (Liste), faq (Frage => Antwort).
 *
 * @return array<string, array<string, mixed>>
 */
function rw24_blueprint_services(): array {
	return array(

		'rohrreinigung' => array(
			'title'     => __( 'Rohrreinigung', 'ruhrwerk24' ),
			'group'     => 'kanaltechnik',
			'icon'      => 'drain',
			'emergency' => true,
			'claim'     => __( 'Abfluss frei in der Regel innerhalb einer Stunde – mit Festpreis vor Arbeitsbeginn.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Verstopfte Abflüsse, Toiletten und Grundleitungen – mechanisch oder per Hochdruck gereinigt.', 'ruhrwerk24' ),
			'content'   => '<p>' . __( 'Wenn das Wasser in Dusche, Spüle oder Toilette steht, zählt jede Minute. Unsere Monteure sind mit Spirale, Hochdruckspüler und Kamera ausgestattet und lösen auch hartnäckige Verstopfungen aus Fett, Kalk oder Wurzeln.', 'ruhrwerk24' ) . '</p>'
				. '<h2>' . __( 'So gehen wir vor', 'ruhrwerk24' ) . '</h2>'
				. '<p>' . __( 'Vor Ort schauen wir uns die Lage an und nennen Ihnen einen verbindlichen Preis, bevor wir anfangen. Erst dann wird gereinigt. Bei wiederkehrenden Verstopfungen prüfen wir die Leitung zusätzlich mit der Kamera, damit das Problem nicht in zwei Wochen wieder auftritt.', 'ruhrwerk24' ) . '</p>',
			'features'  => array(
				__( 'Festpreis vor Arbeitsbeginn', 'ruhrwerk24' ),
				__( 'Hochdruck- und Spiralreinigung', 'ruhrwerk24' ),
				__( 'Kamerakontrolle bei Bedarf', 'ruhrwerk24' ),
				__( 'Notdienst auch nachts und am Wochenende', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Was kostet eine Rohrreinigung?', 'ruhrwerk24' ) => __( 'Das hängt von Ort und Ursache der Verstopfung ab. Sie erhalten vor Arbeitsbeginn einen Festpreis – ohne versteckte Anfahrts- oder Nachtzuschläge im Kleingedruckten.', 'ruhrwerk24' ),
				__( 'Wie schnell sind Sie da?', 'ruhrwerk24' ) => __( 'Im Ruhrgebiet meist innerhalb von 60 Minuten, im übrigen NRW in der Regel am selben Tag.', 'ruhrwerk24' ),
			),
		),

		'kanalsanierung' => array(
			'title'     => __( 'Kanalsanierung', 'ruhrwerk24' ),
			'group'     => 'kanaltechnik',
			'icon'      => 'pipe',
			'emergency' => false,
			'claim'     => __( 'Undichte Leitungen grabenlos sanieren – Garten, Einfahrt und Keller bleiben, wie sie sind.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Inliner, Kurzliner und Roboterverfahren für Grund- und Anschlussleitungen.', 'ruhrwerk24' ),
			'content'   => '<p>' . __( 'Risse, Muffenversatz und Wurzeleinwuchs sind die häufigsten Schäden an Hausanschlüssen. Statt den Garten aufzugraben, ziehen wir einen harzgetränkten Schlauch in die alte Leitung ein und härten ihn aus. Das Ergebnis ist ein neues, nahtloses Rohr im alten.', 'ruhrwerk24' ) . '</p>'
				. '<h2>' . __( 'Grabenlos statt Bagger', 'ruhrwerk24' ) . '</h2>'
				. '<p>' . __( 'Je nach Schadensbild setzen wir Kurzliner für einzelne Stellen oder Schlauchliner für ganze Strecken ein. Sie bekommen vorher die Kamerabilder, eine Empfehlung und ein schriftliches Angebot – und nach der Sanierung eine Abnahme-Befahrung als Nachweis.', 'ruhrwerk24' ) . '</p>',
			'features'  => array(
				__( 'Schlauch- und Kurzliner', 'ruhrwerk24' ),
				__( 'Fräsroboter für Wurzeln und Abzweige', 'ruhrwerk24' ),
				__( 'Abnahme mit Kamera und Protokoll', 'ruhrwerk24' ),
				__( 'Kein Aufgraben, keine Folgekosten', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Wie lange hält eine Inliner-Sanierung?', 'ruhrwerk24' ) => __( 'Die Hersteller geben eine Nutzungsdauer von 50 Jahren an – vergleichbar mit einem neu verlegten Rohr.', 'ruhrwerk24' ),
			),
		),

		'kanal-tv-inspektion' => array(
			'title'     => __( 'Kanal-TV & Dichtheitsprüfung', 'ruhrwerk24' ),
			'group'     => 'kanaltechnik',
			'icon'      => 'camera',
			'emergency' => false,
			'claim'     => __( 'Sehen, was im Rohr los ist: Kamerabefahrung mit Bericht und Video für Sie und Ihre Versicherung.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Schadensanalyse, Leitungsortung und Dichtheitsnachweis nach DIN 1986-30.', 'ruhrwerk24' ),
			'content'   => '<p>' . __( 'Mit unserer Schiebe- und Fahrwagenkamera inspizieren wir Leitungen ab 40 mm Durchmesser. Sie bekommen ein Protokoll mit Fotos, Schadensklassen und Lageplan – die Grundlage für jede Sanierung, jeden Hauskauf und jede Versicherungsmeldung.', 'ruhrwerk24' ) . '</p>'
				. '<h2>' . __( 'Dichtheitsprüfung', 'ruhrwerk24' ) . '</h2>'
				. '<p>' . __( 'Ist sie bei Ihnen vorgeschrieben, prüfen wir Ihre Grundleitungen per Wasser- oder Luftdruck und stellen die Bescheinigung für die Behörde aus.', 'ruhrwerk24' ) . '</p>',
			'features'  => array(
				__( 'Kamera mit Ortungssender', 'ruhrwerk24' ),
				__( 'Protokoll und Video auf USB oder per Link', 'ruhrwerk24' ),
				__( 'Dichtheitsnachweis für Behörden', 'ruhrwerk24' ),
			),
			'faq'       => array(),
		),

		'sanitaer' => array(
			'title'     => __( 'Sanitär', 'ruhrwerk24' ),
			'group'     => 'haustechnik',
			'icon'      => 'droplet',
			'emergency' => true,
			'claim'     => __( 'Rohrbruch, tropfender Hahn oder neues Bad – Meisterbetrieb mit festen Terminen.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Reparatur, Installation und Badsanierung aus einer Hand.', 'ruhrwerk24' ),
			'content'   => '<p>' . __( 'Ein Wasserschaden wartet nicht. Wir sperren ab, orten das Leck und reparieren – auch nachts. Und wenn es nicht eilt, planen wir mit Ihnen Ihr neues Bad: von der barrierefreien Dusche bis zur kompletten Sanierung mit allen Gewerken.', 'ruhrwerk24' ) . '</p>',
			'features'  => array(
				__( 'Rohrbruch und Leckortung', 'ruhrwerk24' ),
				__( 'Armaturen, WC, Spülkästen', 'ruhrwerk24' ),
				__( 'Badsanierung inklusive Fliesen', 'ruhrwerk24' ),
				__( 'Barrierefreie Umbauten', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Übernimmt die Versicherung den Wasserschaden?', 'ruhrwerk24' ) => __( 'Leitungswasserschäden sind meist über die Wohngebäudeversicherung abgedeckt. Wir dokumentieren den Schaden mit Fotos und Bericht, damit die Regulierung reibungslos läuft.', 'ruhrwerk24' ),
			),
		),

		'heizung' => array(
			'title'     => __( 'Heizung & Wärmepumpe', 'ruhrwerk24' ),
			'group'     => 'haustechnik',
			'icon'      => 'flame',
			'emergency' => true,
			'claim'     => __( 'Heizung aus? Wir sind schnell da. Und planen mit Ihnen den Umstieg auf die Wärmepumpe.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Wartung, Störungsdienst und Modernisierung – inklusive Förderberatung.', 'ruhrwerk24' ),
			'content'   => '<p>' . __( 'Eine jährliche Wartung spart Energie und verhindert Ausfälle im Winter. Fällt die Heizung doch einmal aus, kommt unser Notdienst. Für die Modernisierung rechnen wir durch, ob sich eine Wärmepumpe in Ihrem Haus lohnt, und kümmern uns um den Förderantrag.', 'ruhrwerk24' ) . '</p>',
			'features'  => array(
				__( 'Gas-, Öl- und Brennwertgeräte', 'ruhrwerk24' ),
				__( 'Wärmepumpen und Hybridanlagen', 'ruhrwerk24' ),
				__( 'Hydraulischer Abgleich', 'ruhrwerk24' ),
				__( 'Hilfe beim Förderantrag', 'ruhrwerk24' ),
			),
			'faq'       => array(),
		),

		'elektro' => array(
			'title'     => __( 'Elektro', 'ruhrwerk24' ),
			'group'     => 'haustechnik',
			'icon'      => 'bolt',
			'emergency' => false,
			'claim'     => __( 'Sicherer Strom für Haus und Betrieb – vom Zählerschrank bis zur Wallbox.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Installation, E-Check, Zählerschränke, Wallboxen und Photovoltaik-Anschluss.', 'ruhrwerk24' ),
			'content'   => '<p>' . __( 'Alte Leitungen und überlastete Sicherungskästen sind eine häufige Brandursache. Wir prüfen Ihre Anlage, erneuern Zählerschränke nach aktueller Norm und installieren Wallboxen und Speicher – angemeldet beim Netzbetreiber.', 'ruhrwerk24' ) . '</p>',
			'features'  => array(
				__( 'E-Check mit Prüfprotokoll', 'ruhrwerk24' ),
				__( 'Zählerschrank-Erneuerung', 'ruhrwerk24' ),
				__( 'Wallbox und PV-Anschluss', 'ruhrwerk24' ),
			),
			'faq'       => array(),
		),

		'innenausbau' => array(
			'title'     => __( 'Innenausbau', 'ruhrwerk24' ),
			'group'     => 'ausbau',
			'icon'      => 'trowel',
			'emergency' => false,
			'claim'     => __( 'Fliesen, Trockenbau und Malerarbeiten – koordiniert von einem Ansprechpartner.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Renovierung und Ausbau mit allen Gewerken, termintreu und zum Festpreis.', 'ruhrwerk24' ),
			'content'   => '<p>' . __( 'Statt drei Handwerker zu koordinieren, haben Sie bei uns einen Bauleiter. Er stimmt Fliesenleger, Trockenbauer und Maler aufeinander ab, damit keiner auf den anderen wartet – und Sie zum vereinbarten Termin wieder einziehen können.', 'ruhrwerk24' ) . '</p>',
			'features'  => array(
				__( 'Fliesen- und Plattenarbeiten', 'ruhrwerk24' ),
				__( 'Trockenbau und Dämmung', 'ruhrwerk24' ),
				__( 'Maler- und Tapezierarbeiten', 'ruhrwerk24' ),
				__( 'Ein Bauleiter für alle Gewerke', 'ruhrwerk24' ),
			),
			'faq'       => array(),
		),

		'entruempelung' => array(
			'title'     => __( 'Entrümpelung', 'ruhrwerk24' ),
			'group'     => 'ausbau',
			'icon'      => 'box',
			'emergency' => false,
			'claim'     => __( 'Keller, Wohnung oder ganzes Haus – besenrein übergeben, fachgerecht entsorgt.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Räumung und Entsorgung mit Wertanrechnung und Festpreis nach Besichtigung.', 'ruhrwerk24' ),
			'content'   => '<p>' . __( 'Nach einem Todesfall, vor dem Umzug oder wenn der Keller einfach voll ist: Wir räumen, trennen und entsorgen nach Vorschrift. Verwertbares rechnen wir an, und Sie erhalten die Räume besenrein zurück.', 'ruhrwerk24' ) . '</p>',
			'features'  => array(
				__( 'Kostenlose Besichtigung', 'ruhrwerk24' ),
				__( 'Wertanrechnung', 'ruhrwerk24' ),
				__( 'Entsorgungsnachweis', 'ruhrwerk24' ),
				__( 'Besenreine Übergabe', 'ruhrwerk24' ),
			),
			'faq'       => array(),
		),
	);
}
