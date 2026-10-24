<?php
/**
 * Hashtags und Teilen.
 *
 * Für Google sind Hashtags kein Rankingfaktor - ihr Nutzen liegt woanders:
 * Wer eine Seite teilt, bekommt passende Hashtags gleich mit (Reichweite in
 * sozialen Netzen), und jeder Hashtag auf der Seite ist ein interner Link
 * zur passenden Leistungs- oder Stadtseite. Diese interne Verlinkung hilft
 * Suchmaschinen sehr wohl.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Text zu einem Hashtag: "Kanal-TV & Dichtheitsprüfung" -> "#KanalTVDichtheitsprüfung".
 */
function rw24_to_hashtag( string $text ): string {
	// Titel kommen HTML-kodiert ("&#038;") - sonst entstünden Hashtags wie "#Transporte038...".
	$text  = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$words = preg_split( '/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY );

	if ( ! $words ) {
		return '';
	}

	$tag = '';
	foreach ( $words as $word ) {
		$tag .= mb_strtoupper( mb_substr( $word, 0, 1 ) ) . mb_substr( $word, 1 );
	}

	return '#' . $tag;
}

/**
 * Hashtags der aktuellen Seite, jeweils mit Ziel-Link.
 *
 * @return array<int, array{tag: string, url: string}>
 */
function rw24_page_hashtags(): array {
	$tags = array();
	$add  = static function ( string $text, string $url = '' ) use ( &$tags ): void {
		$tag = str_starts_with( $text, '#' ) ? $text : rw24_to_hashtag( $text );
		if ( $tag && ! isset( $tags[ mb_strtolower( $tag ) ] ) ) {
			$tags[ mb_strtolower( $tag ) ] = array( 'tag' => $tag, 'url' => $url );
		}
	};

	$city = rw24_current_city();
	$slug = rw24_current_city_slug();

	if ( is_singular( 'rw_service' ) ) {
		$service = get_queried_object();
		$title   = get_the_title( $service );

		if ( $city ) {
			$add( $title . ' ' . $city['name'], rw24_city_url( $service, $slug ) );
			$add( $city['name'], rw24_city_hub_url( $slug ) );
			$add( $title, (string) get_permalink( $service ) );
			$add( $title . ' ' . $city['region'] );
		} else {
			$add( $title, (string) get_permalink( $service ) );
			$add( $title . ' NRW' );
		}

		// Eigene Hashtags der Leistung aus dem Backend.
		foreach ( preg_split( '/\s+/', (string) get_post_meta( $service->ID, '_rw_hashtags', true ), -1, PREG_SPLIT_NO_EMPTY ) as $own ) {
			$add( '#' . ltrim( $own, '#' ) );
		}
	} elseif ( $city ) {
		$add( 'Handwerker ' . $city['name'], rw24_city_hub_url( $slug ) );
		$add( $city['name'], rw24_city_hub_url( $slug ) );
		foreach ( array_slice( rw24_all_services(), 0, 3 ) as $service ) {
			$add( get_the_title( $service ) . ' ' . $city['name'], rw24_city_url( $service, $slug ) );
		}
	} elseif ( is_tax( 'rw_service_group' ) ) {
		$add( single_term_title( '', false ), (string) get_term_link( get_queried_object() ) );
	}

	foreach ( preg_split( '/\s+/', rw24_contact( 'rw_hashtags' ), -1, PREG_SPLIT_NO_EMPTY ) as $global ) {
		$add( '#' . ltrim( $global, '#' ) );
	}

	return array_values( (array) apply_filters( 'rw24_page_hashtags', $tags ) );
}

/**
 * Teilen-Links (reine Links, keine fremden Scripts).
 *
 * @return array<string, array{label: string, url: string, icon: string}>
 */
function rw24_share_links( string $url, string $title, array $hashtags ): array {
	$plain = array_map( static fn( array $h ): string => ltrim( $h['tag'], '#' ), $hashtags );
	$text  = $title . ' ' . implode( ' ', array_map( static fn( string $t ): string => '#' . $t, array_slice( $plain, 0, 4 ) ) );

	return array(
		'whatsapp' => array( 'label' => 'WhatsApp', 'icon' => 'chat', 'url' => 'https://wa.me/?text=' . rawurlencode( $text . ' ' . $url ) ),
		'facebook' => array( 'label' => 'Facebook', 'icon' => 'star', 'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $url ) ),
		'x'        => array( 'label' => 'X', 'icon' => 'arrow', 'url' => 'https://twitter.com/intent/tweet?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title ) . '&hashtags=' . rawurlencode( implode( ',', array_slice( $plain, 0, 4 ) ) ) ),
		'linkedin' => array( 'label' => 'LinkedIn', 'icon' => 'building', 'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ) ),
		'mail'     => array( 'label' => __( 'E-Mail', 'ruhrwerk24' ), 'icon' => 'mail', 'url' => 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $text . "\n" . $url ) ),
	);
}

/**
 * Hashtags als Keywords-Meta (für Netzwerke und Suchmaschinen, die es lesen).
 */
function rw24_hashtag_meta(): void {
	if ( rw24_seo_plugin_active() || ! ( is_singular( 'rw_service' ) || rw24_is_city_view() || is_tax( 'rw_service_group' ) ) ) {
		return;
	}

	$words = array_map( static fn( array $h ): string => ltrim( $h['tag'], '#' ), rw24_page_hashtags() );

	if ( $words ) {
		printf( '<meta name="keywords" content="%s">' . "\n", esc_attr( implode( ', ', $words ) ) );
	}
}
add_action( 'wp_head', 'rw24_hashtag_meta', 6 );
