<?php
/**
 * Stadt-Landingpages.
 *
 * Zwei Arten virtueller Seiten, ohne einen einzigen Datenbankeintrag:
 *
 *   /rohrreinigung-essen/     Leistung × Stadt  (templates/city-service.php)
 *   /servicegebiete/essen/    alle Leistungen einer Stadt (templates/city-hub.php)
 *
 * Der Trick: Die Regel übergibt den Leistungs-Slug an die Query-Variable
 * des Inhaltstyps. WordPress lädt dadurch ganz normal die Leistung, und
 * Titel, Beitragsbild und Inhalt stehen im Template wie gewohnt bereit.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Regex-Alternation aller Stadt-Slugs.
 */
function rw24_city_pattern(): string {
	return implode( '|', array_map( static fn( string $s ): string => preg_quote( $s, '#' ), array_keys( rw24_cities() ) ) );
}

/**
 * Rewrite-Regeln und Query-Variable.
 */
function rw24_city_rewrites(): void {
	$cities = rw24_city_pattern();

	if ( ! $cities ) {
		return;
	}

	add_rewrite_tag( '%rw_city%', '(' . $cities . ')' );

	add_rewrite_rule( '^servicegebiete/(' . $cities . ')/?$', 'index.php?pagename=servicegebiete-uebersicht&rw_city=$matches[1]', 'top' );
	add_rewrite_rule( '^([a-z0-9-]+)-(' . $cities . ')/?$', 'index.php?rw_service=$matches[1]&rw_city=$matches[2]', 'top' );
}
add_action( 'init', 'rw24_city_rewrites', 5 );

/**
 * Regeln neu schreiben, sobald sich Theme-Version oder Städteliste ändern.
 *
 * Erspart den Gang zu Einstellungen > Permalinks nach jedem Update.
 */
function rw24_maybe_flush_rewrites(): void {
	$hash = md5( RW24_VERSION . '|' . rw24_city_pattern() );

	if ( get_option( 'rw24_rewrite_hash' ) !== $hash ) {
		flush_rewrite_rules( false );
		update_option( 'rw24_rewrite_hash', $hash, true );
	}
}
add_action( 'init', 'rw24_maybe_flush_rewrites', 99 );

/**
 * Kein passender Leistungs-Slug? Dann war eine normale Seite gemeint.
 *
 * Beispiel: Eine Seite "kontakt-essen" würde sonst von der Stadtregel
 * abgefangen und auf einer 404 landen.
 *
 * @param array $vars Geparste Query-Variablen.
 */
function rw24_city_request( array $vars ): array {
	if ( empty( $vars['rw_service'] ) || empty( $vars['rw_city'] ) ) {
		return $vars;
	}

	$exists = get_page_by_path( $vars['rw_service'], OBJECT, 'rw_service' );

	if ( $exists && 'publish' === $exists->post_status ) {
		return $vars;
	}

	return array( 'pagename' => $vars['rw_service'] . '-' . $vars['rw_city'] );
}
add_filter( 'request', 'rw24_city_request' );

/**
 * Slug der aktuellen Stadt oder leerer String.
 */
function rw24_current_city_slug(): string {
	$slug = (string) get_query_var( 'rw_city' );

	return rw24_city( $slug ) ? $slug : '';
}

/**
 * Daten der aktuellen Stadt oder null.
 */
function rw24_current_city(): ?array {
	$slug = rw24_current_city_slug();

	return $slug ? rw24_city( $slug ) : null;
}

/**
 * Ist die aktuelle Seite eine Stadt-Landingpage?
 */
function rw24_is_city_view(): bool {
	if ( ! rw24_current_city_slug() ) {
		return false;
	}

	return is_singular( 'rw_service' ) || is_page( 'servicegebiete-uebersicht' );
}

/**
 * Adresse Leistung × Stadt.
 *
 * @param WP_Post|int|null $service Leistung.
 */
function rw24_city_url( $service, string $city_slug ): string {
	$service = get_post( $service );

	if ( ! $service ) {
		return '';
	}

	return user_trailingslashit( home_url( '/' . $service->post_name . '-' . $city_slug ) );
}

/**
 * Adresse der Stadtübersicht.
 */
function rw24_city_hub_url( string $city_slug ): string {
	return user_trailingslashit( home_url( '/servicegebiete/' . $city_slug ) );
}

/**
 * Passendes Template für Stadtseiten.
 */
function rw24_city_template( string $template ): string {
	if ( ! rw24_is_city_view() ) {
		return $template;
	}

	$file = is_singular( 'rw_service' ) ? 'templates/city-service.php' : 'templates/city-hub.php';
	$path = locate_template( $file );

	return $path ? $path : $template;
}
add_filter( 'template_include', 'rw24_city_template' );

/**
 * Stadtseiten nicht auf die Leistung oder Übersicht umleiten.
 *
 * @param string|false $redirect Ziel der kanonischen Umleitung.
 */
function rw24_city_no_redirect( $redirect ) {
	return rw24_is_city_view() ? false : $redirect;
}
add_filter( 'redirect_canonical', 'rw24_city_no_redirect' );

/**
 * Kanonische Adresse der aktuellen Stadtseite oder leerer String.
 */
function rw24_city_canonical(): string {
	if ( ! rw24_is_city_view() ) {
		return '';
	}

	$slug = rw24_current_city_slug();

	return is_singular( 'rw_service' ) ? rw24_city_url( get_queried_object(), $slug ) : rw24_city_hub_url( $slug );
}

/**
 * Canonical-Tag von WordPress für Stadtseiten korrigieren.
 *
 * Ohne diesen Filter zeigte jede Stadtseite auf die allgemeine
 * Leistungsseite - Google würde die Stadtseiten dann ignorieren.
 *
 * @param string|false $url Kanonische Adresse.
 */
function rw24_city_canonical_url( $url ) {
	$city = rw24_city_canonical();

	return $city ? $city : $url;
}
add_filter( 'get_canonical_url', 'rw24_city_canonical_url' );

/**
 * Seitentitel der Stadtseiten.
 *
 * @param array $parts Titelbestandteile.
 */
function rw24_city_title( array $parts ): array {
	$city = rw24_current_city();

	if ( ! $city || ! rw24_is_city_view() ) {
		return $parts;
	}

	if ( is_singular( 'rw_service' ) ) {
		/* translators: 1: Leistung, 2: Stadt. */
		$parts['title'] = sprintf( __( '%1$s in %2$s', 'ruhrwerk24' ), get_the_title( get_queried_object() ), $city['name'] );
	} else {
		/* translators: %s: Stadt. */
		$parts['title'] = sprintf( __( 'Handwerker in %s – alle Leistungen', 'ruhrwerk24' ), $city['name'] );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'rw24_city_title' );
