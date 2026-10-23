<?php
/**
 * Ruhrwerk24 - Theme-Bootstrap.
 *
 * @package Ruhrwerk24
 * @author  Over-Tech.de
 */

defined( 'ABSPATH' ) || exit;

define( 'RW24_VERSION', '2.3.0' );
define( 'RW24_DIR', get_template_directory() );
define( 'RW24_URI', get_template_directory_uri() );

/**
 * Theme-Support und Bildgrößen.
 *
 * Die Zuschnitte entsprechen den Seitenverhältnissen in tokens.css. Dadurch
 * steht die Bildfläche im Layout fest, bevor ein Foto eingesetzt wird, und
 * es entsteht kein Layout-Shift.
 */
function rw24_setup(): void {
	load_theme_textdomain( 'ruhrwerk24', RW24_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-logo', array( 'height' => 64, 'width' => 240, 'flex-width' => true, 'flex-height' => true ) );

	// Block-Muster von wordpress.org passen nicht zum Design.
	remove_theme_support( 'core-block-patterns' );

	add_editor_style( array( 'assets/css/fonts.css', 'assets/css/tokens.css', 'assets/css/editor.css' ) );

	add_image_size( 'rw24-service-card', 720, 540, true );   // 4:3
	add_image_size( 'rw24-service-hero', 1920, 1080, true ); // 16:9
	add_image_size( 'rw24-reference', 1200, 800, true );     // 3:2
	add_image_size( 'rw24-square', 800, 800, true );         // 1:1

	register_nav_menus(
		array(
			'primary' => __( 'Hauptnavigation', 'ruhrwerk24' ),
			'footer'  => __( 'Footer', 'ruhrwerk24' ),
			'legal'   => __( 'Rechtliches', 'ruhrwerk24' ),
		)
	);
}
add_action( 'after_setup_theme', 'rw24_setup' );

/**
 * Inhaltsbreite für Einbettungen (Videos, iframes).
 */
function rw24_content_width(): void {
	$GLOBALS['content_width'] = 760;
}
add_action( 'after_setup_theme', 'rw24_content_width', 0 );

/**
 * Bildgrößen im Medien-Dialog benennen.
 */
function rw24_image_size_names( array $sizes ): array {
	return array_merge(
		$sizes,
		array(
			'rw24-service-card' => __( 'Leistung – Karte (4:3)', 'ruhrwerk24' ),
			'rw24-service-hero' => __( 'Leistung – Hero (16:9)', 'ruhrwerk24' ),
			'rw24-reference'    => __( 'Referenz (3:2)', 'ruhrwerk24' ),
			'rw24-square'       => __( 'Quadratisch (1:1)', 'ruhrwerk24' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'rw24_image_size_names' );

/**
 * Styles und Scripts.
 *
 * Schriften liegen lokal im Theme - kein Google-Fonts-Aufruf, damit keine
 * IP-Adressen der Besucher an Dritte gehen (DSGVO). Das Script laeuft mit
 * defer und braucht kein jQuery.
 */
function rw24_assets(): void {
	wp_enqueue_style( 'rw24-fonts', RW24_URI . '/assets/css/fonts.css', array(), RW24_VERSION );
	wp_enqueue_style( 'rw24-tokens', RW24_URI . '/assets/css/tokens.css', array( 'rw24-fonts' ), RW24_VERSION );
	wp_enqueue_style( 'rw24-main', RW24_URI . '/assets/css/main.css', array( 'rw24-tokens' ), RW24_VERSION );

	// Farben aus dem Customizer überschreiben die Standardwerte der Tokens.
	wp_add_inline_style( 'rw24-tokens', rw24_color_css() );

	wp_enqueue_script(
		'rw24-main',
		RW24_URI . '/assets/js/main.js',
		array(),
		RW24_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'rw24-main',
		'rw24i18n',
		array(
			'required' => __( 'Bitte wählen Sie eine Antwort.', 'ruhrwerk24' ),
			'zip'      => __( 'Bitte geben Sie eine fünfstellige Postleitzahl ein.', 'ruhrwerk24' ),
			'name'     => __( 'Bitte geben Sie Ihren Namen ein.', 'ruhrwerk24' ),
			'email'    => __( 'Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'ruhrwerk24' ),
			'consent'  => __( 'Bitte bestätigen Sie die Einwilligung.', 'ruhrwerk24' ),
			/* translators: 1: aktuelle Frage, 2: Anzahl Fragen. */
			'status'   => __( 'Frage %1$d von %2$d', 'ruhrwerk24' ),
		)
	);

	// Adressen der 3D-Szenen. main.js lädt sie erst nach dem Seitenaufbau nach.
	wp_add_inline_script(
		'rw24-main',
		'window.rw24=' . wp_json_encode(
			array(
				'scene3d' => add_query_arg( 'ver', RW24_VERSION, RW24_URI . '/assets/js/hero3d.js' ),
				'bg3d'    => add_query_arg( 'ver', RW24_VERSION, RW24_URI . '/assets/js/bg3d.js' ),
			)
		) . ';',
		'before'
	);

	// Block-Styles nur laden, wo Blöcke tatsächlich vorkommen.
	if ( ! is_singular() || ! has_blocks() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'rw24_assets', 20 );

/**
 * Die Hausschrift vorladen - sie trägt jede Überschrift über dem Falz.
 */
function rw24_preload_font(): void {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( RW24_URI . '/assets/fonts/archivo.woff2' )
	);
}
add_action( 'wp_head', 'rw24_preload_font', 1 );

/**
 * Favicon aus dem Theme, solange im Customizer kein Website-Icon gesetzt ist.
 */
function rw24_favicon(): void {
	if ( has_site_icon() ) {
		return;
	}

	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( RW24_URI . '/assets/img/favicon.svg' ) );
}
add_action( 'wp_head', 'rw24_favicon', 2 );

/**
 * Aufräumen: weniger Head-Ballast, bessere Ladezeit.
 */
function rw24_cleanup(): void {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'rw24_cleanup' );

/**
 * Klasse auf <html>, sobald JavaScript läuft.
 *
 * Steht inline im Head, damit mehrstufiges Formular und Einblend-Animationen
 * ohne Flackern starten. Ohne JavaScript bleibt alles sichtbar.
 */
function rw24_js_flag(): void {
	echo "<script>document.documentElement.classList.add('js');</script>\n";
}
add_action( 'wp_head', 'rw24_js_flag', 0 );

/**
 * Body-Klassen für Seitentypen, die das Layout steuern.
 */
function rw24_body_class( array $classes ): array {
	if ( rw24_is_city_view() ) {
		$classes[] = 'is-city';
	}

	if ( is_front_page() ) {
		$classes[] = 'is-front';
	}

	return $classes;
}
add_filter( 'body_class', 'rw24_body_class' );

/**
 * Entwickler-Credit im Footer.
 *
 * Über einen Filter geführt, damit er an genau einer Stelle steht.
 */
function rw24_credit(): string {
	$credit = sprintf(
		/* translators: %s: Link zur Agentur. */
		__( 'Website erstellt von %s', 'ruhrwerk24' ),
		'<a href="https://over-tech.de" rel="noopener" target="_blank">Over-Tech.de</a>'
	);

	return (string) apply_filters( 'rw24_credit', $credit );
}

require_once RW24_DIR . '/inc/content-blueprint.php';
require_once RW24_DIR . '/inc/icons.php';
require_once RW24_DIR . '/inc/post-types.php';
require_once RW24_DIR . '/inc/customizer.php';
require_once RW24_DIR . '/inc/template-helpers.php';
require_once RW24_DIR . '/inc/setup-content.php';
require_once RW24_DIR . '/inc/meta-boxes.php';
require_once RW24_DIR . '/inc/leads.php';
require_once RW24_DIR . '/inc/mail-templates.php';
require_once RW24_DIR . '/inc/lead-form.php';
require_once RW24_DIR . '/inc/cities.php';
require_once RW24_DIR . '/inc/city-pages.php';
require_once RW24_DIR . '/inc/sitemap.php';
require_once RW24_DIR . '/inc/seo.php';
