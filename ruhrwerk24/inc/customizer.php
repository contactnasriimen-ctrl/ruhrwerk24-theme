<?php
/**
 * Customizer: Farben, Kontaktdaten, Startseite, Notdienst, Anfragen.
 *
 * Alles, was der Betrieb selbst ändern soll, steht hier an einer Stelle -
 * Templates lesen die Werte nur über rw24_contact().
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Standardwerte aller Theme-Mods.
 *
 * @return array<string, string|int>
 */
function rw24_defaults(): array {
	return array(
		// Farben.
		'rw_color_primary'   => '#0A2342',
		'rw_color_accent'    => '#F4651A',
		'rw_color_emergency' => '#D7263D',

		// Kontakt.
		'rw_owner'           => 'Ruhrwerk24',
		'rw_street'          => 'Musterstraße 1',
		'rw_zip'             => '45127',
		'rw_city'            => 'Essen',
		'rw_phone'           => '0201 123 456 0',
		'rw_email'           => 'info@ruhrwerk24.de',
		'rw_hours'           => 'Mo–Fr 7–18 Uhr · Notdienst 24/7',
		'rw_whatsapp'        => '',

		// Startseite.
		'rw_hero_eyebrow'    => 'Notdienst aktiv – heute noch vor Ort',
		'rw_hero_title'      => 'Handwerk für ganz NRW. Schnell, sauber, zum Festpreis.',
		'rw_hero_text'       => 'Rohrreinigung, Haustechnik und Ausbau aus einer Hand. Beschreiben Sie kurz, worum es geht – wir melden uns mit einem verbindlichen Angebot.',
		'rw_hero_image'      => 0,
		'rw_fact_1_value'    => '24/7',
		'rw_fact_1_label'    => 'Notdienst',
		'rw_fact_2_value'    => '60',
		'rw_fact_2_label'    => 'Minuten bis vor Ort',
		'rw_fact_3_value'    => '8',
		'rw_fact_3_label'    => 'Gewerke, ein Ansprechpartner',

		// Über uns.
		'rw_about_title'     => 'Handwerk aus dem Ruhrgebiet – mit Anspruch an Sauberkeit und klare Preise.',
		'rw_about_text'      => "Ruhrwerk24 ist entstanden, weil wir selbst erlebt haben, wie mühsam Handwerkersuche sein kann: drei Firmen für ein Bad, Termine, die platzen, und Rechnungen, die nicht zum Angebot passen.\n\nDeshalb arbeiten wir anders. Sie haben einen Ansprechpartner für alle Gewerke, bekommen einen Festpreis, bevor wir anfangen, und erfahren von uns, wenn sich etwas ändert. Unsere Monteure sind fest angestellt, geschult und mit moderner Technik unterwegs – von der Kanalkamera bis zur Wärmebildkamera.\n\nUnd weil Schäden selten zu Geschäftszeiten passieren, ist unser Notdienst rund um die Uhr erreichbar.",

		// Notdienst.
		'rw_em_title'        => 'Wasser steht, Heizung aus, Rohr geplatzt?',
		'rw_em_text'         => 'Unser Notdienst ist rund um die Uhr erreichbar – auch an Wochenenden und Feiertagen. Ein Anruf genügt.',

		// Anfragen.
		'rw_lead_recipient'  => '',
		'rw_lead_retention'  => 12,
	);
}

/**
 * Einen Theme-Mod lesen, mit Standardwert aus rw24_defaults().
 *
 * @param string $key Name des Theme-Mods.
 * @return string
 */
function rw24_contact( string $key ): string {
	$defaults = rw24_defaults();

	return (string) get_theme_mod( $key, $defaults[ $key ] ?? '' );
}

/**
 * Customizer-Einstellungen registrieren.
 */
function rw24_customize_register( WP_Customize_Manager $wp_customize ): void {
	$defaults = rw24_defaults();

	$wp_customize->add_panel(
		'rw24',
		array(
			'title'    => __( 'Ruhrwerk24', 'ruhrwerk24' ),
			'priority' => 30,
		)
	);

	// Hilfsfunktion für einfache Textfelder.
	$add_text = static function ( string $section, string $key, string $label, string $type = 'text', string $description = '' ) use ( $wp_customize, $defaults ): void {
		$sanitize = array(
			'email'    => 'sanitize_email',
			'textarea' => 'sanitize_textarea_field',
			'number'   => 'absint',
		);

		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ] ?? '',
				'sanitize_callback' => $sanitize[ $type ] ?? 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			$key,
			array(
				'label'       => $label,
				'description' => $description,
				'section'     => $section,
				'type'        => $type,
			)
		);
	};

	// Farben.
	$wp_customize->add_section( 'rw24_colors', array( 'title' => __( 'Farben', 'ruhrwerk24' ), 'panel' => 'rw24' ) );

	$colors = array(
		'rw_color_primary'   => __( 'Hauptfarbe (Navy)', 'ruhrwerk24' ),
		'rw_color_accent'    => __( 'Akzentfarbe (Buttons)', 'ruhrwerk24' ),
		'rw_color_emergency' => __( 'Notdienst-Farbe', 'ruhrwerk24' ),
	);

	foreach ( $colors as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control( $wp_customize, $key, array( 'label' => $label, 'section' => 'rw24_colors' ) )
		);
	}

	// Kontakt.
	$wp_customize->add_section( 'rw24_contact', array( 'title' => __( 'Kontaktdaten', 'ruhrwerk24' ), 'panel' => 'rw24' ) );

	$add_text( 'rw24_contact', 'rw_owner', __( 'Firmenname', 'ruhrwerk24' ) );
	$add_text( 'rw24_contact', 'rw_street', __( 'Straße und Hausnummer', 'ruhrwerk24' ) );
	$add_text( 'rw24_contact', 'rw_zip', __( 'PLZ', 'ruhrwerk24' ) );
	$add_text( 'rw24_contact', 'rw_city', __( 'Ort', 'ruhrwerk24' ) );
	$add_text( 'rw24_contact', 'rw_phone', __( 'Telefon', 'ruhrwerk24' ), 'text', __( 'So, wie es auf der Website stehen soll. Der Anruf-Link wird automatisch erzeugt.', 'ruhrwerk24' ) );
	$add_text( 'rw24_contact', 'rw_email', __( 'E-Mail', 'ruhrwerk24' ), 'email' );
	$add_text( 'rw24_contact', 'rw_hours', __( 'Erreichbarkeit', 'ruhrwerk24' ) );
	$add_text( 'rw24_contact', 'rw_whatsapp', __( 'WhatsApp-Nummer (optional)', 'ruhrwerk24' ), 'text', __( 'Leer lassen, wenn kein WhatsApp-Kontakt angeboten wird.', 'ruhrwerk24' ) );

	// Startseite.
	$wp_customize->add_section( 'rw24_hero', array( 'title' => __( 'Startseite', 'ruhrwerk24' ), 'panel' => 'rw24' ) );

	$add_text( 'rw24_hero', 'rw_hero_eyebrow', __( 'Hinweis über der Überschrift', 'ruhrwerk24' ) );
	$add_text( 'rw24_hero', 'rw_hero_title', __( 'Überschrift', 'ruhrwerk24' ) );
	$add_text( 'rw24_hero', 'rw_hero_text', __( 'Einleitung', 'ruhrwerk24' ), 'textarea' );

	$wp_customize->add_setting( 'rw_hero_image', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'rw_hero_image',
			array(
				'label'       => __( 'Hintergrundbild', 'ruhrwerk24' ),
				'description' => __( 'Querformat, mindestens 1920×1080. Wird abgedunkelt, damit der Text lesbar bleibt.', 'ruhrwerk24' ),
				'section'     => 'rw24_hero',
				'mime_type'   => 'image',
			)
		)
	);

	for ( $n = 1; $n <= 3; $n++ ) {
		/* translators: %d: Nummer der Kennzahl. */
		$add_text( 'rw24_hero', "rw_fact_{$n}_value", sprintf( __( 'Kennzahl %d – Wert', 'ruhrwerk24' ), $n ), 'text', 1 === $n ? __( 'Reine Zahlen zählen beim Einblenden hoch.', 'ruhrwerk24' ) : '' );
		/* translators: %d: Nummer der Kennzahl. */
		$add_text( 'rw24_hero', "rw_fact_{$n}_label", sprintf( __( 'Kennzahl %d – Beschriftung', 'ruhrwerk24' ), $n ) );
	}

	// Über uns.
	$wp_customize->add_section( 'rw24_about', array( 'title' => __( 'Über uns (Startseite)', 'ruhrwerk24' ), 'panel' => 'rw24' ) );

	$add_text( 'rw24_about', 'rw_about_title', __( 'Überschrift', 'ruhrwerk24' ) );
	$add_text( 'rw24_about', 'rw_about_text', __( 'Text', 'ruhrwerk24' ), 'textarea', __( 'Leerzeile = neuer Absatz.', 'ruhrwerk24' ) );

	// Notdienst.
	$wp_customize->add_section( 'rw24_emergency', array( 'title' => __( 'Notdienst', 'ruhrwerk24' ), 'panel' => 'rw24' ) );

	$add_text( 'rw24_emergency', 'rw_em_title', __( 'Überschrift', 'ruhrwerk24' ) );
	$add_text( 'rw24_emergency', 'rw_em_text', __( 'Text', 'ruhrwerk24' ), 'textarea' );

	// Anfragen.
	$wp_customize->add_section( 'rw24_leads', array( 'title' => __( 'Anfragen', 'ruhrwerk24' ), 'panel' => 'rw24' ) );

	$add_text( 'rw24_leads', 'rw_lead_recipient', __( 'Empfänger für neue Anfragen', 'ruhrwerk24' ), 'email', __( 'Leer = die E-Mail-Adresse aus den Kontaktdaten.', 'ruhrwerk24' ) );
	$add_text( 'rw24_leads', 'rw_lead_retention', __( 'Anfragen automatisch löschen nach (Monaten)', 'ruhrwerk24' ), 'number', __( 'DSGVO: Daten nicht länger als nötig speichern. 0 = nie automatisch löschen.', 'ruhrwerk24' ) );
}
add_action( 'customize_register', 'rw24_customize_register' );

/**
 * Hex-Farbe in RGB-Anteile.
 *
 * @return int[]
 */
function rw24_rgb( string $hex ): array {
	$hex = ltrim( $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	return array_map( 'hexdec', str_split( substr( str_pad( $hex, 6, '0' ), 0, 6 ), 2 ) );
}

/**
 * Zwei Farben mischen: $weight Anteil von $a (0-1), Rest $b.
 */
function rw24_mix( string $a, string $b, float $weight ): string {
	$ca = rw24_rgb( $a );
	$cb = rw24_rgb( $b );
	$mx = array();

	foreach ( array( 0, 1, 2 ) as $i ) {
		$mx[] = (int) round( $ca[ $i ] * $weight + $cb[ $i ] * ( 1 - $weight ) );
	}

	return vsprintf( '#%02x%02x%02x', $mx );
}

/**
 * Farbe mit Deckkraft als rgb()-Wert.
 */
function rw24_alpha( string $hex, float $alpha ): string {
	return vsprintf( 'rgb(%d %d %d / ', rw24_rgb( $hex ) ) . rtrim( rtrim( number_format( $alpha, 2, '.', '' ), '0' ), '.' ) . ')';
}

/**
 * Alle Farb-Tokens aus den drei Grundfarben.
 *
 * Gleiche Rezepte wie die festen Werte in tokens.css - dort stehen sie
 * vorberechnet, damit auch ältere Browser ohne color-mix() alles zeigen.
 *
 * @return array<string, string>
 */
function rw24_color_tokens( string $navy, string $accent, string $emergency ): array {
	$deep = rw24_mix( $navy, '#000000', .62 );

	return array(
		'--navy'           => $navy,
		'--navy-deep'      => $deep,
		'--navy-lift'      => rw24_mix( $navy, '#1c4a80', .70 ),
		'--navy-soft'      => rw24_mix( $navy, '#ffffff', .08 ),
		'--navy-line'      => rw24_mix( $navy, '#e2e6ec', .35 ),
		'--navy-ring'      => rw24_alpha( $navy, .12 ),
		'--navy-55'        => rw24_alpha( $navy, .55 ),
		'--navy-deep-82'   => rw24_alpha( $deep, .82 ),
		'--accent'         => $accent,
		'--accent-hi'      => rw24_mix( $accent, '#ffffff', .82 ),
		'--accent-lo'      => rw24_mix( $accent, '#000000', .86 ),
		'--accent-ink'     => rw24_mix( $accent, '#000000', .70 ),
		'--accent-soft'    => rw24_mix( $accent, '#ffffff', .12 ),
		'--accent-line'    => rw24_mix( $accent, '#e2e6ec', .40 ),
		'--accent-ring'    => rw24_alpha( $accent, .18 ),
		'--accent-glow'    => rw24_alpha( $accent, .7 ),
		'--emergency'      => $emergency,
		'--emergency-lo'   => rw24_mix( $emergency, '#000000', .85 ),
		'--emergency-soft' => rw24_mix( $emergency, '#ffffff', .10 ),
		'--emergency-dark' => rw24_mix( $emergency, $deep, .35 ),
		'--emergency-tint' => rw24_alpha( $emergency, .22 ),
		'--emergency-glow' => rw24_alpha( $emergency, .6 ),
	);
}

/**
 * CSS-Variablen aus den Customizer-Farben.
 *
 * Solange die Standardfarben gelten, kommt nichts dazu - tokens.css
 * enthält sie bereits.
 */
function rw24_color_css(): string {
	$defaults = rw24_defaults();
	$colors   = array();
	$changed  = false;

	foreach ( array( 'rw_color_primary', 'rw_color_accent', 'rw_color_emergency' ) as $key ) {
		$value          = sanitize_hex_color( rw24_contact( $key ) );
		$colors[ $key ] = $value ? $value : (string) $defaults[ $key ];
		$changed        = $changed || strtolower( $colors[ $key ] ) !== strtolower( (string) $defaults[ $key ] );
	}

	if ( ! $changed ) {
		return '';
	}

	$css = '';
	foreach ( rw24_color_tokens( $colors['rw_color_primary'], $colors['rw_color_accent'], $colors['rw_color_emergency'] ) as $var => $value ) {
		$css .= $var . ':' . $value . ';';
	}

	return ':root{' . $css . '}';
}
