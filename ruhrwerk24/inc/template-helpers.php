<?php
/**
 * Template-Helfer: Kontakt-Links, Leistungen, Bilder, Breadcrumbs.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * tel:-Link aus der Telefonnummer im Customizer.
 *
 * "0201 123 456 0" wird zu "tel:+492011234560".
 */
function rw24_phone_href( string $number = '' ): string {
	$digits = preg_replace( '/[^\d+]/', '', $number ? $number : rw24_contact( 'rw_phone' ) );

	if ( str_starts_with( $digits, '00' ) ) {
		$digits = '+' . substr( $digits, 2 );
	} elseif ( str_starts_with( $digits, '0' ) ) {
		$digits = '+49' . substr( $digits, 1 );
	}

	return 'tel:' . $digits;
}

/**
 * WhatsApp-Link oder leerer String, wenn keine Nummer hinterlegt ist.
 */
function rw24_whatsapp_href(): string {
	$number = rw24_contact( 'rw_whatsapp' );

	if ( ! $number ) {
		return '';
	}

	return 'https://wa.me/' . ltrim( substr( rw24_phone_href( $number ), 4 ), '+' );
}

/**
 * Alle Leistungen, nach Gruppen gebündelt und sortiert.
 *
 * Wird auf jeder Seite mehrfach gebraucht (Footer, Raster, Formular).
 * Das Ergebnis liegt deshalb pro Aufruf im statischen Speicher und
 * zwischen Aufrufen im Objekt-Cache; Speichern einer Leistung leert ihn.
 *
 * @return array<int, array{term: WP_Term, posts: WP_Post[]}>
 */
function rw24_grouped_services(): array {
	static $groups = null;

	if ( null !== $groups ) {
		return $groups;
	}

	$cached = wp_cache_get( 'grouped', 'rw24' );

	if ( is_array( $cached ) ) {
		$groups = $cached;
		return $groups;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'rw_service_group',
			'hide_empty' => true,
		)
	);

	$groups = array();

	if ( is_wp_error( $terms ) ) {
		return $groups;
	}

	usort(
		$terms,
		static fn( WP_Term $a, WP_Term $b ): int => (int) get_term_meta( $a->term_id, '_rw_order', true ) <=> (int) get_term_meta( $b->term_id, '_rw_order', true )
	);

	foreach ( $terms as $term ) {
		$posts = get_posts(
			array(
				'post_type'      => 'rw_service',
				'posts_per_page' => -1,
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy'         => 'rw_service_group',
						'terms'            => $term->term_id,
						'include_children' => false,
					),
				),
			)
		);

		if ( $posts ) {
			$groups[] = array(
				'term'  => $term,
				'posts' => $posts,
			);
		}
	}

	wp_cache_set( 'grouped', $groups, 'rw24', HOUR_IN_SECONDS );

	return $groups;
}

/**
 * Alle Leistungen als flache Liste in Anzeigereihenfolge.
 *
 * @return WP_Post[]
 */
function rw24_all_services(): array {
	$all = array();

	foreach ( rw24_grouped_services() as $group ) {
		foreach ( $group['posts'] as $post ) {
			$all[ $post->ID ] = $post;
		}
	}

	return array_values( $all );
}

/**
 * Leistungs-Cache leeren.
 */
function rw24_flush_service_cache(): void {
	wp_cache_delete( 'grouped', 'rw24' );
}
add_action( 'save_post_rw_service', 'rw24_flush_service_cache' );
add_action( 'deleted_post', 'rw24_flush_service_cache' );
add_action( 'set_object_terms', 'rw24_flush_service_cache' );

/**
 * Bild-ID einer Leistung für eine Verwendung.
 *
 * Heute ist das immer das Beitragsbild. Die Funktion bleibt der einzige
 * Zugriffspunkt, damit später separate Hero-Bilder möglich sind, ohne
 * Templates anzufassen.
 */
function rw24_service_image( int $post_id, string $use = 'card' ): int {
	return (int) apply_filters( 'rw24_service_image', (int) get_post_thumbnail_id( $post_id ), $post_id, $use );
}

/**
 * Icon-Name einer Leistung.
 */
function rw24_service_icon( int $post_id ): string {
	$icon = (string) get_post_meta( $post_id, '_rw_icon', true );

	return $icon ? $icon : 'tool';
}

/**
 * Bild in fester Bildfläche ausgeben.
 *
 * Fehlt das Foto, erscheint eine ruhige Fläche in Markenfarben. Nur
 * angemeldete Redakteure sehen dort den Hinweis, welches Format fehlt -
 * Besucher bekommen kein "Platzhalter"-Schild zu sehen.
 *
 * @param int    $id        Anhang-ID, 0 für Platzhalter.
 * @param string $ratio     card | hero | reference | square.
 * @param string $alt       Alternativtext, falls am Bild keiner hinterlegt ist.
 * @param bool   $eager     true für Bilder im ersten Sichtbereich (LCP).
 * @param string $hint      Hinweis für Redakteure, wenn das Foto fehlt.
 */
function rw24_media( int $id, string $ratio = 'card', string $alt = '', bool $eager = false, string $hint = '' ): void {
	$sizes = array(
		'card'      => array( 'rw24-service-card', '(min-width: 1100px) 360px, (min-width: 700px) 50vw, 100vw' ),
		'hero'      => array( 'rw24-service-hero', '100vw' ),
		'reference' => array( 'rw24-reference', '(min-width: 1100px) 760px, 100vw' ),
		'square'    => array( 'rw24-square', '(min-width: 700px) 400px, 100vw' ),
	);

	[ $size, $sizes_attr ] = $sizes[ $ratio ] ?? $sizes['card'];

	echo '<div class="media media--' . esc_attr( $ratio ) . '">';

	if ( $id && wp_attachment_is_image( $id ) ) {
		$stored_alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );

		echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$id,
			$size,
			false,
			array(
				'alt'           => $stored_alt ? $stored_alt : $alt,
				'sizes'         => $sizes_attr,
				'loading'       => $eager ? 'eager' : 'lazy',
				'fetchpriority' => $eager ? 'high' : 'auto',
				'decoding'      => $eager ? 'sync' : 'async',
			)
		);
	} else {
		echo '<span class="media__placeholder" aria-hidden="true">';

		if ( $hint && current_user_can( 'edit_posts' ) ) {
			echo '<span class="media__hint">' . esc_html( $hint ) . '</span>';
		}

		echo '</span>';
	}

	echo '</div>';
}

/**
 * Zeilen aus einem Textfeld als Liste, leere Zeilen entfernt.
 *
 * @return string[]
 */
function rw24_lines( string $text ): array {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $text ) ) ) );
}

/**
 * Merkmale einer Leistung (eine pro Zeile im Backend).
 *
 * @return string[]
 */
function rw24_service_features( int $post_id ): array {
	return rw24_lines( (string) get_post_meta( $post_id, '_rw_features', true ) );
}

/**
 * Häufige Fragen einer Leistung.
 *
 * Im Backend steht jede Frage in einer Zeile, die Antwort dahinter nach
 * einem senkrechten Strich: "Frage? | Antwort."
 *
 * @return array<int, array{q: string, a: string}>
 */
function rw24_service_faq( int $post_id ): array {
	$faq = array();

	foreach ( rw24_lines( (string) get_post_meta( $post_id, '_rw_faq', true ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );

		if ( 2 === count( $parts ) && $parts[0] && $parts[1] ) {
			$faq[] = array(
				'q' => $parts[0],
				'a' => $parts[1],
			);
		}
	}

	return $faq;
}

/**
 * Lesedauer in Minuten.
 *
 * str_word_count() zählt Umlaute als Worttrenner und liefert für deutsche
 * Texte zu hohe Werte - daher Zählung über Leerraum.
 */
function rw24_reading_time( string $content ): int {
	$words = preg_split( '/\s+/u', trim( wp_strip_all_tags( $content ) ), -1, PREG_SPLIT_NO_EMPTY );

	return max( 1, (int) round( count( (array) $words ) / 200 ) );
}

/**
 * Breadcrumb-Pfad der aktuellen Seite.
 *
 * Eine Quelle für die sichtbare Navigation und das BreadcrumbList-Schema
 * in seo.php, damit beide nie auseinanderlaufen.
 *
 * @return array<int, array{label: string, url: string}>
 */
function rw24_breadcrumb_trail(): array {
	$trail = array(
		array(
			'label' => __( 'Start', 'ruhrwerk24' ),
			'url'   => home_url( '/' ),
		),
	);

	$services_link = (string) get_post_type_archive_link( 'rw_service' );
	$areas_page    = get_page_by_path( 'servicegebiete-uebersicht' );
	$city          = rw24_current_city();

	if ( $city && is_singular( 'rw_service' ) ) {
		$trail[] = array( 'label' => __( 'Servicegebiete', 'ruhrwerk24' ), 'url' => $areas_page ? (string) get_permalink( $areas_page ) : '' );
		$trail[] = array( 'label' => $city['name'], 'url' => rw24_city_hub_url( rw24_current_city_slug() ) );
		$trail[] = array( 'label' => get_the_title(), 'url' => rw24_city_url( get_queried_object(), rw24_current_city_slug() ) );
	} elseif ( $city ) {
		$trail[] = array( 'label' => __( 'Servicegebiete', 'ruhrwerk24' ), 'url' => $areas_page ? (string) get_permalink( $areas_page ) : '' );
		$trail[] = array( 'label' => $city['name'], 'url' => rw24_city_hub_url( rw24_current_city_slug() ) );
	} elseif ( is_singular( 'rw_service' ) ) {
		$trail[] = array( 'label' => __( 'Leistungen', 'ruhrwerk24' ), 'url' => $services_link );
		$groups  = get_the_terms( get_queried_object_id(), 'rw_service_group' );

		if ( $groups && ! is_wp_error( $groups ) ) {
			$trail[] = array( 'label' => $groups[0]->name, 'url' => (string) get_term_link( $groups[0] ) );
		}

		$trail[] = array( 'label' => get_the_title(), 'url' => (string) get_permalink() );
	} elseif ( is_tax( 'rw_service_group' ) ) {
		$trail[] = array( 'label' => __( 'Leistungen', 'ruhrwerk24' ), 'url' => $services_link );
		$trail[] = array( 'label' => single_term_title( '', false ), 'url' => (string) get_term_link( get_queried_object() ) );
	} elseif ( is_post_type_archive( 'rw_service' ) ) {
		$trail[] = array( 'label' => __( 'Leistungen', 'ruhrwerk24' ), 'url' => $services_link );
	} elseif ( is_singular( 'post' ) ) {
		$blog    = (int) get_option( 'page_for_posts' );
		$trail[] = array( 'label' => $blog ? get_the_title( $blog ) : __( 'Ratgeber', 'ruhrwerk24' ), 'url' => $blog ? (string) get_permalink( $blog ) : '' );
		$trail[] = array( 'label' => get_the_title(), 'url' => (string) get_permalink() );
	} elseif ( is_page() && ! is_front_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$trail[] = array( 'label' => get_the_title( $ancestor ), 'url' => (string) get_permalink( $ancestor ) );
		}
		$trail[] = array( 'label' => get_the_title(), 'url' => (string) get_permalink() );
	}

	return (array) apply_filters( 'rw24_breadcrumb_trail', $trail );
}

/**
 * Sichtbare Breadcrumbs. Der letzte Eintrag ist die aktuelle Seite.
 */
function rw24_breadcrumbs(): void {
	$trail = rw24_breadcrumb_trail();

	if ( count( $trail ) < 2 ) {
		return;
	}

	$last = count( $trail ) - 1;

	echo '<nav class="crumbs" aria-label="' . esc_attr__( 'Sie sind hier', 'ruhrwerk24' ) . '"><ol>';

	foreach ( $trail as $i => $item ) {
		echo '<li>';

		if ( $i === $last ) {
			echo '<span aria-current="page">' . esc_html( $item['label'] ) . '</span>';
		} elseif ( $item['url'] ) {
			echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
		} else {
			echo '<span>' . esc_html( $item['label'] ) . '</span>';
		}

		echo '</li>';
	}

	echo '</ol></nav>';
}

/**
 * Menü-Ersatz, solange im Backend kein Menü zugewiesen ist.
 *
 * Ohne diesen Fallback bliebe die Navigation nach einer frischen
 * Installation einfach leer.
 *
 * @param array $args Argumente von wp_nav_menu().
 */
function rw24_nav_fallback( array $args = array() ): void {
	$links = array(
		(string) get_post_type_archive_link( 'rw_service' ) => __( 'Leistungen', 'ruhrwerk24' ),
		home_url( '/notdienst/' )                          => __( 'Notdienst', 'ruhrwerk24' ),
		home_url( '/servicegebiete-uebersicht/' )          => __( 'Servicegebiete', 'ruhrwerk24' ),
		home_url( '/anfrage/' )                            => __( 'Anfrage', 'ruhrwerk24' ),
	);

	$items = '';

	foreach ( $links as $url => $label ) {
		$items .= '<li class="menu-item"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}

	$wrap  = $args['items_wrap'] ?? '<ul id="%1$s" class="%2$s">%3$s</ul>';
	$class = $args['menu_class'] ?? 'menu';
	$html  = sprintf( $wrap, 'rw24-fallback-' . esc_attr( $args['theme_location'] ?? 'menu' ), esc_attr( $class ), $items );

	if ( ! empty( $args['container'] ) ) {
		$html = sprintf(
			'<%1$s class="%2$s" aria-label="%3$s">%4$s</%1$s>',
			tag_escape( $args['container'] ),
			esc_attr( $args['container_class'] ?? '' ),
			esc_attr( $args['container_aria_label'] ?? '' ),
			$html
		);
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oben einzeln escaped.
}

/**
 * Link zur Servicegebiete-Übersicht oder leerer String.
 */
function rw24_areas_url(): string {
	$page = get_page_by_path( 'servicegebiete-uebersicht' );

	return $page ? (string) get_permalink( $page ) : '';
}
