<?php
/**
 * Impressum und Datenschutzerklärung als Shortcodes.
 *
 * [rw24_impressum] und [rw24_datenschutz] setzen die Texte aus den Angaben
 * im Customizer zusammen (Kontaktdaten, Rechtliches, Cookie-Hinweis,
 * Löschfrist der Anfragen). Ändert sich eine Angabe, stimmen beide Seiten
 * automatisch wieder.
 *
 * Die Texte sind eine sorgfältige Vorlage für einen Handwerksbetrieb mit
 * diesem Theme - keine Rechtsberatung. Vor dem Livegang prüfen lassen.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hinweis für Redakteure, wenn eine Pflichtangabe fehlt. Besucher sehen nichts.
 */
function rw24_legal_missing( string $what ): string {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return '';
	}

	return '<span class="legal-missing">' . esc_html(
		sprintf(
			/* translators: %s: fehlende Angabe. */
			__( 'Bitte ergänzen: %s (Customizer › Ruhrwerk24 › Impressum & Datenschutz)', 'ruhrwerk24' ),
			$what
		)
	) . '</span>';
}

/**
 * Anschrift des Betriebs als HTML.
 */
function rw24_legal_address(): string {
	return esc_html( rw24_contact( 'rw_owner' ) ) . '<br>'
		. esc_html( rw24_contact( 'rw_street' ) ) . '<br>'
		. esc_html( trim( rw24_contact( 'rw_zip' ) . ' ' . rw24_contact( 'rw_city' ) ) );
}

/**
 * Impressum.
 */
function rw24_shortcode_impressum(): string {
	$c   = static fn( string $key ): string => trim( rw24_contact( $key ) );
	$out = '<div class="legal">';

	$out .= '<h2>' . esc_html__( 'Angaben gemäß § 5 DDG', 'ruhrwerk24' ) . '</h2>';
	$out .= '<p>' . rw24_legal_address() . '</p>';

	// Fehlende Angaben: Redakteure sehen einen Hinweis, Besucher keine leere Überschrift.
	$block = static function ( string $title, string $value, string $missing ): string {
		$body = $value ? esc_html( $value ) : rw24_legal_missing( $missing );
		return $body ? '<h3>' . esc_html( $title ) . '</h3><p>' . $body . '</p>' : '';
	};

	$out .= $block( __( 'Vertreten durch', 'ruhrwerk24' ), $c( 'rw_legal_rep' ), __( 'Vertretungsberechtigte Person', 'ruhrwerk24' ) );

	$out .= '<h3>' . esc_html__( 'Kontakt', 'ruhrwerk24' ) . '</h3>';
	$out .= '<p>' . esc_html__( 'Telefon:', 'ruhrwerk24' ) . ' <a href="' . esc_url( rw24_phone_href() ) . '">' . esc_html( $c( 'rw_phone' ) ) . '</a><br>'
		. esc_html__( 'E-Mail:', 'ruhrwerk24' ) . ' <a href="mailto:' . esc_attr( $c( 'rw_email' ) ) . '">' . esc_html( $c( 'rw_email' ) ) . '</a></p>';

	if ( $c( 'rw_legal_register' ) ) {
		$out .= '<h3>' . esc_html__( 'Registereintrag', 'ruhrwerk24' ) . '</h3><p>' . esc_html( $c( 'rw_legal_register' ) ) . '</p>';
	}

	$vat = $c( 'rw_legal_vat' )
		? esc_html__( 'Umsatzsteuer-Identifikationsnummer gemäß § 27 a Umsatzsteuergesetz:', 'ruhrwerk24' ) . ' ' . esc_html( $c( 'rw_legal_vat' ) )
		: rw24_legal_missing( __( 'Umsatzsteuer-ID (falls vorhanden)', 'ruhrwerk24' ) );
	if ( $vat ) {
		$out .= '<h3>' . esc_html__( 'Umsatzsteuer-ID', 'ruhrwerk24' ) . '</h3><p>' . $vat . '</p>';
	}

	if ( $c( 'rw_legal_profession' ) || $c( 'rw_legal_chamber' ) ) {
		$out .= '<h3>' . esc_html__( 'Berufsbezeichnung und berufsrechtliche Regelungen', 'ruhrwerk24' ) . '</h3><p>';
		if ( $c( 'rw_legal_profession' ) ) {
			$out .= esc_html__( 'Berufsbezeichnung:', 'ruhrwerk24' ) . ' ' . esc_html( $c( 'rw_legal_profession' ) ) . '<br>';
		}
		if ( $c( 'rw_legal_chamber' ) ) {
			$out .= esc_html__( 'Zuständige Kammer:', 'ruhrwerk24' ) . ' ' . esc_html( $c( 'rw_legal_chamber' ) ) . '<br>';
		}
		$out .= esc_html__( 'Geltende Regelungen: Handwerksordnung (HwO), einsehbar unter gesetze-im-internet.de.', 'ruhrwerk24' ) . '</p>';
	}

	if ( $c( 'rw_legal_insurance' ) ) {
		$out .= '<h3>' . esc_html__( 'Berufshaftpflichtversicherung', 'ruhrwerk24' ) . '</h3><p>' . nl2br( esc_html( $c( 'rw_legal_insurance' ) ) ) . '</p>';
	}

	$out .= '<h3>' . esc_html__( 'Verbraucherstreitbeilegung', 'ruhrwerk24' ) . '</h3>';
	$out .= '<p>' . esc_html__( 'Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.', 'ruhrwerk24' ) . '</p>';

	$out .= '<h3>' . esc_html__( 'Haftung für Inhalte und Links', 'ruhrwerk24' ) . '</h3>';
	$out .= '<p>' . esc_html__( 'Wir erstellen die Inhalte dieser Website mit Sorgfalt, übernehmen aber keine Gewähr für Vollständigkeit und Aktualität. Für Inhalte externer Websites, auf die wir verlinken, sind ausschließlich deren Betreiber verantwortlich. Werden uns Rechtsverletzungen bekannt, entfernen wir die betreffenden Inhalte oder Links umgehend.', 'ruhrwerk24' ) . '</p>';

	return $out . '</div>';
}
add_shortcode( 'rw24_impressum', 'rw24_shortcode_impressum' );

/**
 * Datenschutzerklärung.
 */
function rw24_shortcode_datenschutz(): string {
	$c         = static fn( string $key ): string => trim( rw24_contact( $key ) );
	$months    = (int) rw24_contact( 'rw_lead_retention' );
	$stats     = $c( 'rw_consent_stats_name' );
	$whatsapp  = $c( 'rw_whatsapp' );
	$h2        = static fn( string $t ): string => '<h2>' . esc_html( $t ) . '</h2>';
	$p         = static fn( string $t ): string => '<p>' . esc_html( $t ) . '</p>';

	$out = '<div class="legal">';

	$out .= $h2( __( '1. Verantwortlicher', 'ruhrwerk24' ) );
	$out .= '<p>' . rw24_legal_address() . '<br>'
		. esc_html__( 'Telefon:', 'ruhrwerk24' ) . ' ' . esc_html( $c( 'rw_phone' ) ) . '<br>'
		. esc_html__( 'E-Mail:', 'ruhrwerk24' ) . ' <a href="mailto:' . esc_attr( $c( 'rw_email' ) ) . '">' . esc_html( $c( 'rw_email' ) ) . '</a></p>';

	if ( $c( 'rw_legal_dpo' ) ) {
		$out .= '<h3>' . esc_html__( 'Datenschutzbeauftragte(r)', 'ruhrwerk24' ) . '</h3><p>' . nl2br( esc_html( $c( 'rw_legal_dpo' ) ) ) . '</p>';
	}

	$out .= $h2( __( '2. Das Wichtigste in Kürze', 'ruhrwerk24' ) );
	$out .= '<ul>'
		. '<li>' . esc_html__( 'Diese Website lädt keine Schriften, Skripte oder Grafiken von fremden Servern. Auch die 3D-Darstellungen laufen vollständig aus unserem eigenen Webspace.', 'ruhrwerk24' ) . '</li>'
		. '<li>' . esc_html__( 'Wir setzen keine Cookies zu Werbe- oder Analysezwecken, solange Sie nicht ausdrücklich zustimmen.', 'ruhrwerk24' ) . '</li>'
		. '<li>' . esc_html__( 'Daten aus dem Anfrageformular nutzen wir nur, um Ihre Anfrage zu bearbeiten, und geben sie nicht weiter.', 'ruhrwerk24' ) . '</li>'
		. '</ul>';

	$out .= $h2( __( '3. Hosting und Server-Logdateien', 'ruhrwerk24' ) );
	$out .= '<p>' . ( $c( 'rw_legal_hosting' )
		? esc_html__( 'Diese Website wird betrieben bei:', 'ruhrwerk24' ) . '<br>' . nl2br( esc_html( $c( 'rw_legal_hosting' ) ) )
		: rw24_legal_missing( __( 'Hosting-Anbieter', 'ruhrwerk24' ) ) ) . '</p>';
	$out .= $p( __( 'Beim Aufruf der Website verarbeitet der Server technisch notwendige Daten: IP-Adresse, Datum und Uhrzeit, aufgerufene Seite, übertragene Datenmenge, Browser und Betriebssystem sowie die zuvor besuchte Seite. Das ist nötig, um die Website sicher und stabil auszuliefern (Art. 6 Abs. 1 lit. f DSGVO). Die Logdateien werden nach den Vorgaben des Hosters nach kurzer Zeit gelöscht. Mit dem Hoster besteht ein Vertrag zur Auftragsverarbeitung.', 'ruhrwerk24' ) );

	$out .= $h2( __( '4. Anfrageformular', 'ruhrwerk24' ) );
	$out .= $p( __( 'Wenn Sie uns über das Formular eine Anfrage senden, verarbeiten wir Ihre Angaben: gewünschte Leistung, Dringlichkeit, Art des Objekts, Postleitzahl und Ort, Ihre Beschreibung, Name, Telefonnummer (freiwillig) und E-Mail-Adresse. Wir nutzen diese Daten, um Ihre Anfrage zu beantworten und ein Angebot zu erstellen (Art. 6 Abs. 1 lit. b DSGVO). Sie erhalten eine Bestätigung per E-Mail mit Ihrer Anfragenummer.', 'ruhrwerk24' ) );
	$out .= $p(
		$months > 0
			/* translators: %d: Monate. */
			? sprintf( __( 'Anfragen werden in unserem Website-System gespeichert und nach %d Monaten automatisch gelöscht, sofern kein Auftrag zustande kommt. Kommt ein Auftrag zustande, gelten die gesetzlichen Aufbewahrungsfristen.', 'ruhrwerk24' ), $months )
			: __( 'Anfragen werden in unserem Website-System gespeichert und gelöscht, sobald sie für die Bearbeitung nicht mehr erforderlich sind. Kommt ein Auftrag zustande, gelten die gesetzlichen Aufbewahrungsfristen.', 'ruhrwerk24' )
	);
	$out .= $p( __( 'Zum Schutz vor automatisierten Spam-Einsendungen speichern wir für kurze Zeit eine verschlüsselte, nicht rückrechenbare Kennung Ihrer IP-Adresse (Art. 6 Abs. 1 lit. f DSGVO).', 'ruhrwerk24' ) );

	$out .= $h2( __( '5. Kontakt per E-Mail, Telefon oder WhatsApp', 'ruhrwerk24' ) );
	$out .= $p( __( 'Wenn Sie uns per E-Mail oder Telefon kontaktieren, verarbeiten wir Ihre Angaben zur Bearbeitung Ihres Anliegens (Art. 6 Abs. 1 lit. b DSGVO).', 'ruhrwerk24' ) );
	if ( $whatsapp ) {
		$out .= $p( __( 'Für die Kontaktaufnahme per WhatsApp gelten zusätzlich die Datenschutzbestimmungen der WhatsApp Ireland Limited. Dabei können Daten in Länder außerhalb der EU übertragen werden. Nutzen Sie diesen Weg nur, wenn Sie damit einverstanden sind – Telefon und E-Mail stehen Ihnen jederzeit offen.', 'ruhrwerk24' ) );
	}

	$out .= $h2( __( '6. Cookies und Einwilligung', 'ruhrwerk24' ) );
	$out .= $p( __( 'Diese Website setzt von sich aus keine Cookies. Ihre Auswahl im Cookie-Hinweis speichern wir im lokalen Speicher Ihres Browsers (Schlüssel „rw24-consent“), damit der Hinweis nicht bei jedem Aufruf erscheint. Diese Speicherung ist technisch erforderlich (§ 25 Abs. 2 TDDDG).', 'ruhrwerk24' ) );
	if ( $stats ) {
		$out .= $p(
			sprintf(
				/* translators: %s: Name des Statistik-Dienstes. */
				__( 'Nur wenn Sie der Kategorie „Statistik“ zustimmen, nutzen wir %s, um zu verstehen, wie die Website genutzt wird (Art. 6 Abs. 1 lit. a DSGVO, § 25 Abs. 1 TDDDG). Ohne Ihre Zustimmung wird dieser Dienst nicht geladen.', 'ruhrwerk24' ),
				$stats
			)
		);
	}
	$out .= '<p>' . esc_html__( 'Ihre Einwilligung können Sie jederzeit widerrufen oder ändern:', 'ruhrwerk24' ) . ' <button type="button" class="link-button" data-consent-open>' . esc_html__( 'Cookie-Einstellungen öffnen', 'ruhrwerk24' ) . '</button></p>';

	$out .= $h2( __( '7. Teilen-Funktion', 'ruhrwerk24' ) );
	$out .= $p( __( 'Die Teilen-Schaltflächen sind einfache Links. Erst wenn Sie darauf klicken, öffnet sich die Seite des jeweiligen Netzwerks – vorher werden keine Daten an WhatsApp, Facebook, X oder LinkedIn übertragen.', 'ruhrwerk24' ) );

	$out .= $h2( __( '8. Ihre Rechte', 'ruhrwerk24' ) );
	$out .= $p( __( 'Sie haben das Recht auf Auskunft (Art. 15 DSGVO), Berichtigung (Art. 16), Löschung (Art. 17), Einschränkung der Verarbeitung (Art. 18), Datenübertragbarkeit (Art. 20) und Widerspruch gegen Verarbeitungen auf Grundlage berechtigter Interessen (Art. 21). Eine erteilte Einwilligung können Sie jederzeit mit Wirkung für die Zukunft widerrufen (Art. 7 Abs. 3). Eine formlose Nachricht an die oben genannte Adresse genügt.', 'ruhrwerk24' ) );
	$out .= $p( __( 'Sie haben außerdem das Recht, sich bei einer Datenschutz-Aufsichtsbehörde zu beschweren, zum Beispiel bei der Landesbeauftragten für Datenschutz und Informationsfreiheit Nordrhein-Westfalen (LDI NRW), Kavalleriestraße 2–4, 40213 Düsseldorf.', 'ruhrwerk24' ) );

	$out .= $h2( __( '9. Verschlüsselung', 'ruhrwerk24' ) );
	$out .= $p( __( 'Diese Website nutzt eine TLS-Verschlüsselung. Sie erkennen sie am Schloss-Symbol und an „https://“ in der Adresszeile.', 'ruhrwerk24' ) );

	$out .= '<p class="legal__stand">' . esc_html(
		sprintf(
			/* translators: %s: Monat und Jahr. */
			__( 'Stand: %s', 'ruhrwerk24' ),
			wp_date( 'F Y' )
		)
	) . '</p>';

	return $out . '</div>';
}
add_shortcode( 'rw24_datenschutz', 'rw24_shortcode_datenschutz' );

/**
 * Inhalt der Rechtsseiten als Block-Shortcode.
 */
function rw24_legal_page_content( string $which ): string {
	return '<!-- wp:shortcode -->[rw24_' . $which . ']<!-- /wp:shortcode -->';
}

/**
 * Bestehende Platzhalter-Seiten (vor 2.7) auf die Shortcodes umstellen.
 *
 * Nur, wenn noch der ursprüngliche Hinweis "Bitte ergänzen" drinsteht -
 * selbst geschriebene Texte bleiben unangetastet.
 */
function rw24_upgrade_legal_pages(): void {
	foreach ( array( 'impressum' => 'impressum', 'datenschutz' => 'datenschutz' ) as $slug => $which ) {
		$page = get_page_by_path( $slug );

		if ( $page && str_contains( $page->post_content, 'Bitte ergänzen' ) && ! str_contains( $page->post_content, '[rw24_' ) ) {
			wp_update_post( array( 'ID' => $page->ID, 'post_content' => rw24_legal_page_content( $which ) ) );
		}
	}
}
