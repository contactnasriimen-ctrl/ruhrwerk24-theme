<?php
/**
 * Inhalts-Bauplan: Leistungsgruppen und Leistungen.
 *
 * Wird beim Aktivieren des Themes angelegt (setup-content.php). Danach
 * gehören die Texte der Redaktion: Ein Update ersetzt nur Texte, die seit
 * dem Anlegen niemand verändert hat (Prüfsumme in _rw_blueprint_hash).
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
	// Umzug & Transport steht seit 2.7 vorn - das Kerngeschäft zuerst.
	return array(
		'transport'    => array(
			'name'        => __( 'Umzug & Transport', 'ruhrwerk24' ),
			'description' => __( 'Privatumzug, Firmenumzug oder ein einzelnes Möbelstück: Wir packen, tragen, fahren und bauen wieder auf – mit eigenem Team und Festpreis.', 'ruhrwerk24' ),
		),
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
 * Gruppe, deren Leistungen die 3D-Transporter-Szene bekommen.
 */
function rw24_transport_group(): string {
	return (string) apply_filters( 'rw24_transport_group', 'transport' );
}

/**
 * Gehört eine Leistung zu Umzug & Transport?
 */
function rw24_is_transport( int $post_id ): bool {
	return has_term( rw24_transport_group(), 'rw_service_group', $post_id );
}

/**
 * HTML-Bausteine für die Texte unten - hält den Bauplan lesbar.
 */
function rw24_bp_p( string ...$paragraphs ): string {
	return implode( '', array_map( static fn( string $p ): string => '<p>' . $p . '</p>', $paragraphs ) );
}

function rw24_bp_h2( string $title ): string {
	return '<h2>' . $title . '</h2>';
}

function rw24_bp_list( array $items, bool $ordered = false ): string {
	$tag = $ordered ? 'ol' : 'ul';

	return '<' . $tag . '>' . implode( '', array_map( static fn( string $i ): string => '<li>' . $i . '</li>', $items ) ) . '</' . $tag . '>';
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
			'content'   => rw24_bp_p(
				__( 'Wenn das Wasser in Dusche, Spüle oder Toilette steht, zählt jede Minute. Unsere Monteure sind mit Spirale, Hochdruckspüler und Kamera ausgestattet und lösen auch hartnäckige Verstopfungen aus Fett, Kalk, Haaren oder Wurzeln – vom Waschbecken bis zum Anschluss an den öffentlichen Kanal.', 'ruhrwerk24' ),
				__( 'Wichtig ist uns, nicht nur das Symptom zu beseitigen. Kommt eine Verstopfung immer wieder, steckt fast immer eine Ursache dahinter: ein abgesacktes Rohr, eine verschobene Muffe oder Wurzeln, die durch eine undichte Stelle wachsen. Die finden wir – und sagen Ihnen ehrlich, ob sich eine Sanierung lohnt oder eine regelmäßige Reinigung reicht.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Was wir reinigen', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( 'Waschbecken, Dusche, Badewanne und Küchenspüle', 'ruhrwerk24' ),
						__( 'Toiletten und Urinale', 'ruhrwerk24' ),
						__( 'Fall- und Sammelleitungen in Mehrfamilienhäusern', 'ruhrwerk24' ),
						__( 'Grundleitungen, Hofabläufe und Kellerabläufe', 'ruhrwerk24' ),
						__( 'Dach- und Regenrinnen-Fallrohre', 'ruhrwerk24' ),
						__( 'Fettabscheider-Zuläufe in Gastronomie und Gewerbe', 'ruhrwerk24' ),
					)
				)
				. rw24_bp_h2( __( 'So läuft der Einsatz ab', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( '<strong>Anruf oder Anfrage:</strong> Sie schildern kurz, was passiert ist. Wir nennen Ihnen eine Ankunftszeit.', 'ruhrwerk24' ),
						__( '<strong>Festpreis vor Ort:</strong> Der Monteur sieht sich die Lage an und nennt Ihnen den Preis, bevor er anfängt.', 'ruhrwerk24' ),
						__( '<strong>Reinigung:</strong> mit Spirale oder Hochdruck – je nachdem, was das Rohr verträgt.', 'ruhrwerk24' ),
						__( '<strong>Kontrolle:</strong> Bei wiederkehrenden Problemen schauen wir mit der Kamera nach der Ursache.', 'ruhrwerk24' ),
						__( '<strong>Sauber übergeben:</strong> Wir hinterlassen den Arbeitsbereich so, wie wir ihn vorgefunden haben.', 'ruhrwerk24' ),
					),
					true
				)
				. rw24_bp_h2( __( 'Gut zu wissen', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Bitte keine chemischen Rohrreiniger in großen Mengen verwenden: Sie greifen Dichtungen und ältere Rohre an und lösen die eigentliche Verstopfung oft nicht. Eine Saugglocke ist für den Anfang besser. Wenn die nicht hilft, rufen Sie uns an.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'Festpreis vor Arbeitsbeginn', 'ruhrwerk24' ),
				__( 'Hochdruck- und Spiralreinigung', 'ruhrwerk24' ),
				__( 'Kamerakontrolle bei Bedarf', 'ruhrwerk24' ),
				__( 'Notdienst auch nachts und am Wochenende', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Was kostet eine Rohrreinigung?', 'ruhrwerk24' ) => __( 'Das hängt von Ort und Ursache der Verstopfung ab. Sie erhalten vor Arbeitsbeginn einen Festpreis – ohne versteckte Anfahrts- oder Nachtzuschläge im Kleingedruckten.', 'ruhrwerk24' ),
				__( 'Wie schnell sind Sie da?', 'ruhrwerk24' ) => __( 'Im Ruhrgebiet meist innerhalb von 60 Minuten, im übrigen NRW in der Regel am selben Tag.', 'ruhrwerk24' ),
				__( 'Zahlt der Vermieter oder die Versicherung?', 'ruhrwerk24' ) => __( 'Bei Verstopfungen in gemeinsam genutzten Leitungen ist meist der Vermieter zuständig. Liegt die Ursache im eigenen Waschbecken, in der Regel der Mieter. Wir stellen die Rechnung so aus, wie Sie sie brauchen.', 'ruhrwerk24' ),
			),
		),

		'kanalsanierung' => array(
			'title'     => __( 'Kanalsanierung', 'ruhrwerk24' ),
			'group'     => 'kanaltechnik',
			'icon'      => 'pipe',
			'emergency' => false,
			'claim'     => __( 'Undichte Leitungen grabenlos sanieren – Garten, Einfahrt und Keller bleiben, wie sie sind.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Inliner, Kurzliner und Roboterverfahren für Grund- und Anschlussleitungen.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Risse, Muffenversatz und Wurzeleinwuchs sind die häufigsten Schäden an Hausanschlüssen. Statt den Garten aufzugraben, ziehen wir einen harzgetränkten Schlauch in die alte Leitung ein und härten ihn aus. Das Ergebnis ist ein neues, nahtloses Rohr im alten – ohne Bagger, ohne zerstörte Pflasterung, meist an einem Tag.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Grabenlos statt Bagger', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Je nach Schadensbild setzen wir Kurzliner für einzelne Stellen oder Schlauchliner für ganze Strecken ein. Einragende Wurzeln und Abzweige fräsen wir vorher mit einem Roboter frei. Sie bekommen vorher die Kamerabilder, eine Empfehlung und ein schriftliches Angebot – und nach der Sanierung eine Abnahme-Befahrung als Nachweis.', 'ruhrwerk24' )
				)
				. rw24_bp_h2( __( 'Unsere Verfahren', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( '<strong>Kurzliner:</strong> für einzelne Risse und Muffen, Länge 0,5 bis 2 Meter', 'ruhrwerk24' ),
						__( '<strong>Schlauchliner:</strong> für durchgehend beschädigte Strecken bis 50 Meter', 'ruhrwerk24' ),
						__( '<strong>Hutprofil:</strong> dichtet den Übergang vom Hausanschluss zum Hauptkanal ab', 'ruhrwerk24' ),
						__( '<strong>Fräsroboter:</strong> entfernt Wurzeln, Ablagerungen und einragende Stutzen', 'ruhrwerk24' ),
					)
				)
				. rw24_bp_h2( __( 'Wann ist eine Sanierung nötig?', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Undichte Leitungen lassen Abwasser ins Erdreich und Grundwasser in den Keller. Auf Dauer unterspülen sie Fundamente und Gehwege. Spätestens bei einem Hausverkauf, einer Umnutzung oder wenn die Stadt es verlangt, muss der Zustand nachgewiesen werden. Eine Kamerabefahrung schafft Klarheit, bevor Folgeschäden entstehen.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'Schlauch- und Kurzliner', 'ruhrwerk24' ),
				__( 'Fräsroboter für Wurzeln und Abzweige', 'ruhrwerk24' ),
				__( 'Abnahme mit Kamera und Protokoll', 'ruhrwerk24' ),
				__( 'Kein Aufgraben, keine Folgekosten', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Wie lange hält eine Inliner-Sanierung?', 'ruhrwerk24' ) => __( 'Die Hersteller geben eine Nutzungsdauer von 50 Jahren an – vergleichbar mit einem neu verlegten Rohr.', 'ruhrwerk24' ),
				__( 'Kann ich während der Sanierung im Haus bleiben?', 'ruhrwerk24' ) => __( 'Ja. Nur Toiletten und Abflüsse sind für einige Stunden gesperrt. Wir sagen Ihnen vorher genau, wann.', 'ruhrwerk24' ),
			),
		),

		'kanal-tv-inspektion' => array(
			'title'     => __( 'Kanal-TV & Dichtheitsprüfung', 'ruhrwerk24' ),
			'group'     => 'kanaltechnik',
			'icon'      => 'camera',
			'emergency' => false,
			'claim'     => __( 'Sehen, was im Rohr los ist: Kamerabefahrung mit Bericht und Video für Sie und Ihre Versicherung.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Schadensanalyse, Leitungsortung und Dichtheitsnachweis nach DIN 1986-30.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Mit unserer Schiebe- und Fahrwagenkamera inspizieren wir Leitungen ab 40 mm Durchmesser. Sie bekommen ein Protokoll mit Fotos, Schadensklassen und Lageplan – die Grundlage für jede Sanierung, jeden Hauskauf und jede Versicherungsmeldung.', 'ruhrwerk24' ),
				__( 'Ein Sender am Kamerakopf zeigt uns von oben, wo genau die Leitung verläuft und in welcher Tiefe ein Schaden liegt. Das spart Aufgrabungen an der falschen Stelle.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Wofür Sie eine Kamerabefahrung brauchen', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( 'Vor dem Kauf eines Hauses – versteckte Schäden kosten schnell fünfstellig', 'ruhrwerk24' ),
						__( 'Bei wiederkehrenden Verstopfungen oder Gerüchen', 'ruhrwerk24' ),
						__( 'Bei Feuchtigkeit im Keller ohne erkennbare Ursache', 'ruhrwerk24' ),
						__( 'Als Nachweis für Versicherung oder Behörde', 'ruhrwerk24' ),
						__( 'Vor einer Sanierung, um das richtige Verfahren zu wählen', 'ruhrwerk24' ),
					)
				)
				. rw24_bp_h2( __( 'Dichtheitsprüfung', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Ist sie bei Ihnen vorgeschrieben, prüfen wir Ihre Grundleitungen per Wasser- oder Luftdruck und stellen die Bescheinigung für die Behörde aus. Ob für Ihr Grundstück eine Pflicht besteht, hängt von der Satzung Ihrer Gemeinde und der Lage ab – etwa in Wasserschutzgebieten. Wir klären das vorab für Sie.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'Kamera mit Ortungssender', 'ruhrwerk24' ),
				__( 'Protokoll und Video auf USB oder per Link', 'ruhrwerk24' ),
				__( 'Dichtheitsnachweis für Behörden', 'ruhrwerk24' ),
				__( 'Lageplan der Leitungen', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Wie lange dauert eine Kamerabefahrung?', 'ruhrwerk24' ) => __( 'Für ein Einfamilienhaus meist ein bis zwei Stunden. Das Protokoll erhalten Sie in der Regel am nächsten Werktag.', 'ruhrwerk24' ),
			),
		),

		'sanitaer' => array(
			'title'     => __( 'Sanitär', 'ruhrwerk24' ),
			'group'     => 'haustechnik',
			'icon'      => 'droplet',
			'emergency' => true,
			'claim'     => __( 'Rohrbruch, tropfender Hahn oder neues Bad – Meisterbetrieb mit festen Terminen.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Reparatur, Installation und Badsanierung aus einer Hand.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Ein Wasserschaden wartet nicht. Wir sperren ab, orten das Leck und reparieren – auch nachts. Und wenn es nicht eilt, planen wir mit Ihnen Ihr neues Bad: von der barrierefreien Dusche bis zur kompletten Sanierung mit allen Gewerken.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Reparatur und Notdienst', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Tropfende Armaturen, laufende Spülkästen, undichte Siphons oder ein geplatztes Rohr in der Wand: Unsere Monteure haben die gängigen Ersatzteile im Wagen. Leckagen in Wand und Boden orten wir mit Feuchtemessung und Thermografie, damit nur dort geöffnet wird, wo es nötig ist.', 'ruhrwerk24' )
				)
				. rw24_bp_h2( __( 'Ihr neues Bad', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( 'Beratung vor Ort mit Aufmaß und Ideen für Ihren Grundriss', 'ruhrwerk24' ),
						__( 'Planung mit Festpreis und festem Zeitplan', 'ruhrwerk24' ),
						__( 'Rückbau, Installation, Fliesen, Elektro und Maler aus einer Hand', 'ruhrwerk24' ),
						__( 'Bodengleiche Duschen und barrierefreie Lösungen', 'ruhrwerk24' ),
						__( 'Übergabe mit Einweisung und Gewährleistung', 'ruhrwerk24' ),
					),
					true
				)
				. rw24_bp_h2( __( 'Förderung für barrierefreie Bäder', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Für altersgerechte Umbauten gibt es je nach Situation Zuschüsse, zum Beispiel über die Pflegekasse oder Förderprogramme. Wir sagen Ihnen, was in Frage kommt, und liefern die Unterlagen, die Sie für den Antrag brauchen.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'Rohrbruch und Leckortung', 'ruhrwerk24' ),
				__( 'Armaturen, WC, Spülkästen', 'ruhrwerk24' ),
				__( 'Badsanierung inklusive Fliesen', 'ruhrwerk24' ),
				__( 'Barrierefreie Umbauten', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Übernimmt die Versicherung den Wasserschaden?', 'ruhrwerk24' ) => __( 'Leitungswasserschäden sind meist über die Wohngebäudeversicherung abgedeckt. Wir dokumentieren den Schaden mit Fotos und Bericht, damit die Regulierung reibungslos läuft.', 'ruhrwerk24' ),
				__( 'Wie lange dauert eine Badsanierung?', 'ruhrwerk24' ) => __( 'Ein durchschnittliches Bad ist in zwei bis drei Wochen fertig. Den genauen Zeitplan bekommen Sie mit dem Angebot.', 'ruhrwerk24' ),
			),
		),

		'heizung' => array(
			'title'     => __( 'Heizung & Wärmepumpe', 'ruhrwerk24' ),
			'group'     => 'haustechnik',
			'icon'      => 'flame',
			'emergency' => true,
			'claim'     => __( 'Heizung aus? Wir sind schnell da. Und planen mit Ihnen den Umstieg auf die Wärmepumpe.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Wartung, Störungsdienst und Modernisierung – inklusive Förderberatung.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Eine jährliche Wartung spart Energie und verhindert Ausfälle im Winter. Fällt die Heizung doch einmal aus, kommt unser Notdienst. Für die Modernisierung rechnen wir durch, ob sich eine Wärmepumpe in Ihrem Haus lohnt, und kümmern uns um den Förderantrag.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Wartung und Störungsdienst', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Wir warten Gas-, Öl- und Brennwertgeräte aller gängigen Hersteller: Brenner reinigen, Abgaswerte messen, Sicherheitseinrichtungen prüfen. Bei einer Störung lesen wir den Fehlerspeicher aus und reparieren meist direkt vor Ort.', 'ruhrwerk24' )
				)
				. rw24_bp_h2( __( 'Lohnt sich eine Wärmepumpe bei mir?', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Auch in vielen Bestandsgebäuden – wenn Heizflächen und Vorlauftemperatur passen. Wir prüfen das bei einem Vor-Ort-Termin und rechnen Ihnen ehrlich vor, was die Anlage kostet, was sie spart und welche Förderung möglich ist. Wo es nicht passt, sagen wir es auch.', 'ruhrwerk24' )
				)
				. rw24_bp_list(
					array(
						__( 'Heizlastberechnung für Ihr Haus', 'ruhrwerk24' ),
						__( 'Prüfung von Heizkörpern und Vorlauftemperatur', 'ruhrwerk24' ),
						__( 'Hybridlösungen mit bestehendem Kessel', 'ruhrwerk24' ),
						__( 'Hydraulischer Abgleich für gleichmäßige Wärme', 'ruhrwerk24' ),
					)
				),
			'features'  => array(
				__( 'Gas-, Öl- und Brennwertgeräte', 'ruhrwerk24' ),
				__( 'Wärmepumpen und Hybridanlagen', 'ruhrwerk24' ),
				__( 'Hydraulischer Abgleich', 'ruhrwerk24' ),
				__( 'Hilfe beim Förderantrag', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Wie oft sollte die Heizung gewartet werden?', 'ruhrwerk24' ) => __( 'Einmal im Jahr, am besten vor der Heizperiode. Viele Hersteller machen die Wartung zur Bedingung für die Garantie.', 'ruhrwerk24' ),
			),
		),

		'elektro' => array(
			'title'     => __( 'Elektro', 'ruhrwerk24' ),
			'group'     => 'haustechnik',
			'icon'      => 'bolt',
			'emergency' => false,
			'claim'     => __( 'Sicherer Strom für Haus und Betrieb – vom Zählerschrank bis zur Wallbox.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Installation, E-Check, Zählerschränke, Wallboxen und Photovoltaik-Anschluss.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Alte Leitungen und überlastete Sicherungskästen sind eine häufige Brandursache. Wir prüfen Ihre Anlage, erneuern Zählerschränke nach aktueller Norm und installieren Wallboxen und Speicher – angemeldet beim Netzbetreiber.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Unsere Leistungen', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( 'E-Check für Wohnung, Haus und Gewerbe mit Prüfprotokoll', 'ruhrwerk24' ),
						__( 'Zählerschrank-Erneuerung inklusive Anmeldung beim Netzbetreiber', 'ruhrwerk24' ),
						__( 'Fehlerstrom-Schutzschalter nachrüsten', 'ruhrwerk24' ),
						__( 'Wallbox-Installation für Ihr E-Auto', 'ruhrwerk24' ),
						__( 'Anschluss von Photovoltaik-Anlage und Speicher', 'ruhrwerk24' ),
						__( 'Elektroinstallation bei Sanierung und Umbau', 'ruhrwerk24' ),
					)
				)
				. rw24_bp_h2( __( 'Wann der Zählerschrank zu alt ist', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Schraubsicherungen, kein Fehlerstrom-Schutzschalter oder ein Zählerschrank aus Holz sind deutliche Zeichen. Spätestens wenn eine Wallbox, eine Wärmepumpe oder eine PV-Anlage dazukommt, verlangt der Netzbetreiber meist einen Schrank nach aktuellem Stand. Wir schauen ihn uns an und sagen Ihnen, was nötig ist – und was nicht.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'E-Check mit Prüfprotokoll', 'ruhrwerk24' ),
				__( 'Zählerschrank-Erneuerung', 'ruhrwerk24' ),
				__( 'Wallbox und PV-Anschluss', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Brauche ich für eine Wallbox eine Genehmigung?', 'ruhrwerk24' ) => __( 'Wallboxen bis 11 kW müssen beim Netzbetreiber angemeldet werden, stärkere Geräte genehmigt. Beides übernehmen wir für Sie.', 'ruhrwerk24' ),
			),
		),

		'innenausbau' => array(
			'title'     => __( 'Innenausbau', 'ruhrwerk24' ),
			'group'     => 'ausbau',
			'icon'      => 'trowel',
			'emergency' => false,
			'claim'     => __( 'Fliesen, Trockenbau und Malerarbeiten – koordiniert von einem Ansprechpartner.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Renovierung und Ausbau mit allen Gewerken, termintreu und zum Festpreis.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Statt drei Handwerker zu koordinieren, haben Sie bei uns einen Bauleiter. Er stimmt Fliesenleger, Trockenbauer und Maler aufeinander ab, damit keiner auf den anderen wartet – und Sie zum vereinbarten Termin wieder einziehen können.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Was wir für Sie ausbauen', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( 'Wohnungsrenovierung bei Mieterwechsel oder Einzug', 'ruhrwerk24' ),
						__( 'Dachgeschoss- und Kellerausbau', 'ruhrwerk24' ),
						__( 'Neue Raumaufteilung mit Trockenbauwänden', 'ruhrwerk24' ),
						__( 'Fliesen in Bad, Küche und Flur', 'ruhrwerk24' ),
						__( 'Maler-, Tapezier- und Spachtelarbeiten bis Q4', 'ruhrwerk24' ),
						__( 'Wiederherstellung nach Wasserschäden', 'ruhrwerk24' ),
					)
				)
				. rw24_bp_h2( __( 'Termintreue, die man planen kann', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Sie bekommen mit dem Angebot einen Bauzeitenplan. Jeden Freitag erhalten Sie eine kurze Nachricht mit dem Stand der Dinge und Fotos. Verschiebt sich etwas, erfahren Sie es von uns – nicht, weil plötzlich niemand auf der Baustelle ist.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'Fliesen- und Plattenarbeiten', 'ruhrwerk24' ),
				__( 'Trockenbau und Dämmung', 'ruhrwerk24' ),
				__( 'Maler- und Tapezierarbeiten', 'ruhrwerk24' ),
				__( 'Ein Bauleiter für alle Gewerke', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Arbeiten Sie auch für Vermieter und Hausverwaltungen?', 'ruhrwerk24' ) => __( 'Ja, regelmäßig. Für wiederkehrende Renovierungen bei Mieterwechsel vereinbaren wir gern feste Preise und Ansprechpartner.', 'ruhrwerk24' ),
			),
		),

		'entruempelung' => array(
			'title'     => __( 'Entrümpelung', 'ruhrwerk24' ),
			'group'     => 'ausbau',
			'icon'      => 'box',
			'emergency' => false,
			'claim'     => __( 'Keller, Wohnung oder ganzes Haus – besenrein übergeben, fachgerecht entsorgt.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Räumung und Entsorgung mit Wertanrechnung und Festpreis nach Besichtigung.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Nach einem Todesfall, vor dem Umzug oder wenn der Keller einfach voll ist: Wir räumen, trennen und entsorgen nach Vorschrift. Verwertbares rechnen wir an, und Sie erhalten die Räume besenrein zurück.', 'ruhrwerk24' ),
				__( 'Gerade bei einer Haushaltsauflösung nach einem Trauerfall nehmen wir uns Zeit. Persönliche Dokumente, Fotos und Wertsachen, die wir beim Räumen finden, legen wir für Sie beiseite.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'So läuft eine Entrümpelung ab', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( '<strong>Kostenlose Besichtigung:</strong> Wir sehen uns Umfang und Zugänge an.', 'ruhrwerk24' ),
						__( '<strong>Festpreis:</strong> inklusive Entsorgung, abzüglich Wertanrechnung.', 'ruhrwerk24' ),
						__( '<strong>Räumung:</strong> meist an einem Tag, auch ohne Ihre Anwesenheit.', 'ruhrwerk24' ),
						__( '<strong>Übergabe:</strong> besenrein, auf Wunsch mit Schlüsselübergabe an den Vermieter.', 'ruhrwerk24' ),
					),
					true
				)
				. rw24_bp_h2( __( 'Fachgerecht entsorgt', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Elektrogeräte, Sondermüll, Holz und Metall trennen wir und bringen sie zu zertifizierten Annahmestellen. Auf Wunsch erhalten Sie einen Entsorgungsnachweis. Gut erhaltene Möbel geben wir, wo möglich, an soziale Einrichtungen weiter.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'Kostenlose Besichtigung', 'ruhrwerk24' ),
				__( 'Wertanrechnung', 'ruhrwerk24' ),
				__( 'Entsorgungsnachweis', 'ruhrwerk24' ),
				__( 'Besenreine Übergabe', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Muss ich bei der Räumung dabei sein?', 'ruhrwerk24' ) => __( 'Nein. Viele Kunden übergeben uns den Schlüssel und kommen erst zur Abnahme. Wir schicken Ihnen Fotos vom Ergebnis.', 'ruhrwerk24' ),
			),
		),

		// Seit 2.5.0: Umzug & Transport. "since" sorgt dafür, dass ein Update
		// diese Leistungen auf bestehenden Seiten ergänzt - einmal, nicht wieder.
		'umzug' => array(
			'title'     => __( 'Umzug', 'ruhrwerk24' ),
			'group'     => 'transport',
			'icon'      => 'truck',
			'emergency' => false,
			'since'     => '2.5.0',
			'claim'     => __( 'Vom ersten Karton bis zum letzten Bild an der Wand – Ihr Umzug zum Festpreis.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Privatumzüge in NRW und deutschlandweit: verpacken, tragen, fahren, Möbel wieder aufbauen.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Ein Umzug ist anstrengend genug. Wir nehmen Ihnen ab, was Sie nicht selbst machen wollen – oder gleich alles. Unser eigenes Team packt, schützt Möbel und Böden, trägt, fährt und baut am neuen Ort wieder auf. Sie bekommen nach der Besichtigung einen Festpreis, der nicht mehr steigt.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Was wir für Sie übernehmen', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( 'Besichtigung vor Ort oder per Video – kostenlos', 'ruhrwerk24' ),
						__( 'Umzugskartons, Packpapier und Kleiderboxen', 'ruhrwerk24' ),
						__( 'Ein- und Auspacken auf Wunsch', 'ruhrwerk24' ),
						__( 'Möbel ab- und wieder aufbauen, Küchen montieren', 'ruhrwerk24' ),
						__( 'Halteverbotszone beantragen und aufstellen', 'ruhrwerk24' ),
						__( 'Entrümpelung und Endreinigung der alten Wohnung', 'ruhrwerk24' ),
					)
				)
				. rw24_bp_h2( __( 'So läuft Ihr Umzug ab', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( '<strong>Besichtigung:</strong> Wir sehen uns Umfang, Etagen und Wege an – vor Ort oder per Videoanruf.', 'ruhrwerk24' ),
						__( '<strong>Festpreis:</strong> schriftlich, mit allen Leistungen. Ohne Nachberechnung für Stockwerke oder Laufwege.', 'ruhrwerk24' ),
						__( '<strong>Vorbereitung:</strong> Kartons kommen zu Ihnen, die Halteverbotszone steht am Umzugstag.', 'ruhrwerk24' ),
						__( '<strong>Umzugstag:</strong> Ein fester Teamleiter führt das Team und ist Ihr Ansprechpartner.', 'ruhrwerk24' ),
						__( '<strong>Übergabe:</strong> Möbel stehen, Lampen hängen, die alte Wohnung ist besenrein.', 'ruhrwerk24' ),
					),
					true
				)
				. rw24_bp_h2( __( 'Gut zu wissen', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Die Arbeitskosten eines Privatumzugs lassen sich als haushaltsnahe Dienstleistung steuerlich geltend machen. Wir weisen sie auf der Rechnung getrennt aus – fragen Sie Ihre Steuerberatung, was in Ihrem Fall gilt.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'Festpreis nach Besichtigung', 'ruhrwerk24' ),
				__( 'Verpackungsservice und Material', 'ruhrwerk24' ),
				__( 'Möbel- und Küchenmontage', 'ruhrwerk24' ),
				__( 'Halteverbotszone inklusive', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Wie früh sollte ich den Umzug buchen?', 'ruhrwerk24' ) => __( 'Am besten vier bis sechs Wochen vorher. Zum Monatsende sind die Termine schnell vergeben – kurzfristige Umzüge machen wir trotzdem möglich, wenn es geht.', 'ruhrwerk24' ),
				__( 'Was passiert, wenn etwas beschädigt wird?', 'ruhrwerk24' ) => __( 'Wir haften im Rahmen der gesetzlichen Regeln für Umzugsunternehmen. Für besonders wertvolle Stücke vereinbaren wir vorab gern eine Zusatzversicherung.', 'ruhrwerk24' ),
			),
		),

		'firmenumzug' => array(
			'title'     => __( 'Firmenumzug', 'ruhrwerk24' ),
			'group'     => 'transport',
			'icon'      => 'building',
			'emergency' => false,
			'since'     => '2.5.0',
			'claim'     => __( 'Freitag zugeschlossen, Montag weitergearbeitet – Büro- und Betriebsumzüge ohne Stillstand.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Büros, Praxen, Werkstätten: geplant mit Projektleiter, umgesetzt nach Feierabend oder am Wochenende.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Jeder Tag, an dem Ihr Betrieb stillsteht, kostet. Deshalb planen wir Firmenumzüge so, dass Ihre Mitarbeiter am Freitag den Rechner ausschalten und ihn am Montag am neuen Platz wieder einschalten. Ein Projektleiter koordiniert alles – vom Etikettensystem bis zum Anschluss der Arbeitsplätze.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Unsere Leistungen', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( 'Umzugsplanung mit Zeitplan und Raumplan', 'ruhrwerk24' ),
						__( 'Etikettensystem: jeder Karton findet seinen Platz', 'ruhrwerk24' ),
						__( 'Abbau und Aufbau von Büromöbeln und Arbeitsplätzen', 'ruhrwerk24' ),
						__( 'Sicherer Transport von IT, Akten und Archiven', 'ruhrwerk24' ),
						__( 'Elektro- und Netzwerkanschlüsse durch unsere Elektriker', 'ruhrwerk24' ),
						__( 'Entsorgung von Altmobiliar mit Nachweis', 'ruhrwerk24' ),
					)
				)
				. rw24_bp_h2( __( 'Alles aus einer Hand', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Weil Elektro, Trockenbau und Malerarbeiten bei uns im Haus sind, wird die neue Fläche nicht nur bezogen, sondern auch fertig gemacht: Steckdosen, Trennwände und frische Farbe – ohne drei weitere Firmen zu koordinieren.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'Fester Projektleiter', 'ruhrwerk24' ),
				__( 'Umzug nach Feierabend oder am Wochenende', 'ruhrwerk24' ),
				__( 'IT- und Aktentransport', 'ruhrwerk24' ),
				__( 'Elektro und Ausbau aus eigener Hand', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Können Sie auch am Wochenende umziehen?', 'ruhrwerk24' ) => __( 'Ja, das ist bei Firmenumzügen sogar die Regel. So bleibt Ihr Betrieb unter der Woche erreichbar.', 'ruhrwerk24' ),
			),
		),

		'transporte' => array(
			'title'     => __( 'Transporte & Möbellieferung', 'ruhrwerk24' ),
			'group'     => 'transport',
			'icon'      => 'package',
			'emergency' => false,
			'since'     => '2.5.0',
			'claim'     => __( 'Ein Sofa, ein Schrank, eine Waschmaschine – kurzfristig abgeholt und sicher geliefert.', 'ruhrwerk24' ),
			'excerpt'   => __( 'Einzeltransporte, Lieferung vom Möbelhaus, Mitnahme von Altgeräten – auch kurzfristig.', 'ruhrwerk24' ),
			'content'   => rw24_bp_p(
				__( 'Nicht jeder Transport ist ein Umzug. Manchmal ist es nur das neue Sofa vom Möbelhaus, der Kleiderschrank von Oma oder die Waschmaschine in den dritten Stock. Dafür gibt es uns auch: mit Transporter, zwei kräftigen Händepaaren und Werkzeug für den Aufbau.', 'ruhrwerk24' )
			)
				. rw24_bp_h2( __( 'Was wir transportieren', 'ruhrwerk24' ) )
				. rw24_bp_list(
					array(
						__( 'Einzelne Möbelstücke und Kleintransporte', 'ruhrwerk24' ),
						__( 'Abholung und Lieferung vom Möbelhaus inklusive Aufbau', 'ruhrwerk24' ),
						__( 'Waschmaschinen, Trockner und Kühlschränke – auf Wunsch angeschlossen', 'ruhrwerk24' ),
						__( 'Mitnahme und Entsorgung des Altgeräts', 'ruhrwerk24' ),
						__( 'Transporte für Händler und Gewerbe', 'ruhrwerk24' ),
					)
				)
				. rw24_bp_h2( __( 'Kurzfristig und planbar', 'ruhrwerk24' ) )
				. rw24_bp_p(
					__( 'Für Einzeltransporte bekommen Sie meist innerhalb weniger Tage einen Termin, oft auch am selben Tag. Den Preis nennen wir vorab – auf Grundlage von Strecke, Stockwerken und Gewicht.', 'ruhrwerk24' )
				),
			'features'  => array(
				__( 'Termine oft am selben Tag', 'ruhrwerk24' ),
				__( 'Aufbau und Anschluss', 'ruhrwerk24' ),
				__( 'Altgeräte-Mitnahme', 'ruhrwerk24' ),
				__( 'Festpreis vorab', 'ruhrwerk24' ),
			),
			'faq'       => array(
				__( 'Schließen Sie die Waschmaschine auch an?', 'ruhrwerk24' ) => __( 'Ja. Unsere Sanitärer schließen Wasch- und Spülmaschinen fachgerecht an – das Altgerät nehmen wir auf Wunsch gleich mit.', 'ruhrwerk24' ),
			),
		),
	);
}

/**
 * Allgemeine Fragen für die Startseite.
 *
 * @return array<int, array{q: string, a: string}>
 */
function rw24_home_faq(): array {
	return (array) apply_filters(
		'rw24_home_faq',
		array(
			array( 'q' => __( 'In welchen Städten sind Sie im Einsatz?', 'ruhrwerk24' ), 'a' => __( 'Im ganzen Ruhrgebiet, im Rheinland, im Bergischen Land, am Niederrhein und im Münsterland. Eine Übersicht finden Sie unter Servicegebiete.', 'ruhrwerk24' ) ),
			array( 'q' => __( 'Was kostet die Anfahrt?', 'ruhrwerk24' ), 'a' => __( 'Die Anfahrt ist im Festpreis enthalten, den Sie vor Arbeitsbeginn bekommen. Es gibt keine Nachberechnung für Wegezeiten.', 'ruhrwerk24' ) ),
			array( 'q' => __( 'Gibt es Zuschläge nachts und am Wochenende?', 'ruhrwerk24' ), 'a' => __( 'Ja, im Notdienst außerhalb der Geschäftszeiten. Die nennen wir Ihnen schon am Telefon – damit Sie entscheiden können, ob es bis zum nächsten Werktag warten kann.', 'ruhrwerk24' ) ),
			array( 'q' => __( 'Wie kann ich bezahlen?', 'ruhrwerk24' ), 'a' => __( 'Per Rechnung, EC-Karte vor Ort oder Überweisung. Für größere Aufträge vereinbaren wir Abschlagszahlungen nach Baufortschritt.', 'ruhrwerk24' ) ),
			array( 'q' => __( 'Arbeiten Sie auch für Hausverwaltungen und Gewerbe?', 'ruhrwerk24' ), 'a' => __( 'Ja. Für Verwaltungen, Wohnungsgesellschaften und Betriebe bieten wir Rahmenverträge mit festen Ansprechpartnern und Reaktionszeiten.', 'ruhrwerk24' ) ),
			array( 'q' => __( 'Gibt es Gewährleistung?', 'ruhrwerk24' ), 'a' => __( 'Selbstverständlich – nach den gesetzlichen Vorgaben. Bei Sanierungen erhalten Sie zusätzlich ein Abnahmeprotokoll.', 'ruhrwerk24' ) ),
		)
	);
}

/**
 * Typische Gegebenheiten je Region - macht jede Stadtseite eigenständig.
 *
 * @return array{title: string, text: string}|null
 */
function rw24_region_text( string $region, string $city ): ?array {
	$texts = array(
		'Ruhrgebiet'      => __( 'Viele Häuser in %s stammen aus der Nachkriegszeit oder stehen in ehemaligen Zechensiedlungen. Die Grundleitungen sind oft aus Steinzeug mit Muffen, in die Wurzeln einwachsen – und Bergsenkungen können Leitungen zusätzlich verschieben. Eine Kamerabefahrung zeigt schnell, ob das bei Ihnen der Fall ist.', 'ruhrwerk24' ),
		'Rheinland'       => __( 'Zwischen Gründerzeit-Altbau in der Innenstadt und Neubaugebieten am Stadtrand ist in %s alles dabei. Bei Starkregen und hohem Rheinpegel drückt Wasser zurück in die Kanäle – eine funktionierende Rückstauklappe ist dann Gold wert. Wir prüfen und warten sie für Sie.', 'ruhrwerk24' ),
		'Bergisches Land' => __( 'Hanglagen und viel Niederschlag: In %s fließt Regenwasser schnell und in großen Mengen ab. Dachentwässerung, Drainagen und Rückstausicherung verdienen hier besondere Aufmerksamkeit – genau wie Leitungen, die am Hang arbeiten.', 'ruhrwerk24' ),
		'Niederrhein'     => __( 'Am Niederrhein steht das Grundwasser vielerorts hoch. Undichte Leitungen und Kellerwände zeigen sich in %s deshalb oft früher als anderswo. Eine Kamerainspektion und eine Dichtheitsprüfung schaffen Klarheit, bevor Feuchtigkeit Schaden anrichtet.', 'ruhrwerk24' ),
		'Münsterland'     => __( 'Rund um %s prägen Einfamilienhäuser und ältere Hofstellen das Bild. Lange Grundleitungen bis zum öffentlichen Kanal und alte Anschlüsse ehemaliger Klärgruben sind keine Seltenheit – wir kennen die typischen Schwachstellen.', 'ruhrwerk24' ),
	);

	if ( empty( $texts[ $region ] ) ) {
		return null;
	}

	return array(
		/* translators: %s: Stadt. */
		'title' => sprintf( __( 'Was wir in %s häufig sehen', 'ruhrwerk24' ), $city ),
		'text'  => sprintf( $texts[ $region ], $city ),
	);
}
