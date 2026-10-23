<?php
/**
 * SEO: Meta-Description, Open Graph, Schema.org, Robots.
 *
 * Ist Yoast, Rank Math oder SEOPress aktiv, überlässt das Theme diesen
 * Plugins Description, Open Graph und Breadcrumb-Schema. Das LocalBusiness-
 * und Service-Schema bleibt, weil die Plugins es ohne Zusatzmodule nicht
 * mit Einsatzgebieten und Leistungen befüllen.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ist ein SEO-Plugin aktiv?
 */
function rw24_seo_plugin_active(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || class_exists( 'AIOSEO\\Plugin\\AIOSEO' );
}

/**
 * Text auf Description-Länge kürzen, ohne Wörter zu zerschneiden.
 */
function rw24_trim_description( string $text, int $max = 155 ): string {
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $text ) ) ) );

	if ( mb_strlen( $text ) <= $max ) {
		return $text;
	}

	$cut = mb_substr( $text, 0, $max - 1 );
	$cut = preg_replace( '/\s+\S*$/u', '', $cut );

	return rtrim( $cut, ' ,;:–-' ) . '…';
}

/**
 * Meta-Description der aktuellen Seite.
 */
function rw24_meta_description(): string {
	$city = rw24_current_city();

	if ( $city && is_singular( 'rw_service' ) ) {
		$parts = implode( ', ', array_slice( $city['parts'], 0, 3 ) );
		$text  = sprintf(
			/* translators: 1: Leistung, 2: Stadt, 3: Stadtteile, 4: Kurzbeschreibung. */
			__( '%1$s in %2$s und allen Stadtteilen wie %3$s. %4$s', 'ruhrwerk24' ),
			get_the_title( get_queried_object() ),
			$city['name'],
			$parts,
			get_post_meta( get_queried_object_id(), '_rw_short_claim', true )
		);
	} elseif ( $city ) {
		$names = wp_list_pluck( rw24_all_services(), 'post_title' );
		$text  = sprintf(
			/* translators: 1: Firma, 2: Stadt, 3: Leistungen. */
			__( '%1$s in %2$s: %3$s. Ein Ansprechpartner, Festpreis vor Arbeitsbeginn, Notdienst rund um die Uhr.', 'ruhrwerk24' ),
			get_bloginfo( 'name' ),
			$city['name'],
			implode( ', ', array_slice( $names, 0, 5 ) )
		);
	} elseif ( is_front_page() ) {
		$text = rw24_contact( 'rw_hero_text' );
	} elseif ( is_singular( 'rw_service' ) ) {
		$claim = (string) get_post_meta( get_queried_object_id(), '_rw_short_claim', true );
		$text  = $claim ? $claim : get_the_excerpt( get_queried_object() );
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$text = has_excerpt( $post ) ? get_the_excerpt( $post ) : $post->post_content;
	} elseif ( is_tax() || is_category() ) {
		$text = term_description();
	} elseif ( is_post_type_archive( 'rw_service' ) ) {
		$text = __( 'Kanaltechnik, Haustechnik, Ausbau und Räumung: alle Leistungen von Ruhrwerk24 im Überblick – ein Ansprechpartner für ganz NRW.', 'ruhrwerk24' );
	} else {
		$text = get_bloginfo( 'description' );
	}

	return rw24_trim_description( (string) apply_filters( 'rw24_meta_description', (string) $text ) );
}

/**
 * Vorschaubild für Social Media (URL) oder leerer String.
 */
function rw24_share_image(): string {
	$id = 0;

	if ( is_singular() ) {
		$id = (int) get_post_thumbnail_id( get_queried_object_id() );
	}

	if ( ! $id ) {
		$id = (int) get_theme_mod( 'rw_hero_image', 0 );
	}

	$src = $id ? wp_get_attachment_image_url( $id, 'rw24-service-hero' ) : '';

	return $src ? $src : '';
}

/**
 * Description und Open-Graph-Tags.
 */
function rw24_meta_tags(): void {
	if ( rw24_seo_plugin_active() || is_404() ) {
		return;
	}

	$description = rw24_meta_description();
	$title       = wp_get_document_title();
	$url         = rw24_city_canonical();

	if ( ! $url ) {
		$url = is_singular() ? (string) wp_get_canonical_url() : '';
	}

	if ( ! $url && is_front_page() ) {
		$url = home_url( '/' );
	}

	$tags = array(
		'description'         => $description,
		'og:type'             => is_singular( 'post' ) ? 'article' : 'website',
		'og:locale'           => str_replace( '-', '_', get_bloginfo( 'language' ) ),
		'og:site_name'        => get_bloginfo( 'name' ),
		'og:title'            => $title,
		'og:description'      => $description,
		'og:url'              => $url,
		'og:image'            => rw24_share_image(),
		'twitter:card'        => 'summary_large_image',
	);

	foreach ( $tags as $name => $content ) {
		if ( '' === $content ) {
			continue;
		}

		$attr = str_starts_with( $name, 'og:' ) ? 'property' : 'name';

		printf( '<meta %1$s="%2$s" content="%3$s">' . "\n", esc_attr( $attr ), esc_attr( $name ), esc_attr( $content ) );
	}
}
add_action( 'wp_head', 'rw24_meta_tags', 5 );

/**
 * Schema.org als JSON-LD-Graph.
 */
function rw24_schema(): void {
	if ( is_404() ) {
		return;
	}

	$home     = home_url( '/' );
	$business = $home . '#business';
	$graph    = array();

	$area = array();
	foreach ( rw24_cities() as $city ) {
		$area[] = array(
			'@type' => 'City',
			'name'  => $city['name'],
		);
	}

	$org = array(
		'@type'      => 'HomeAndConstructionBusiness',
		'@id'        => $business,
		'name'       => get_bloginfo( 'name' ),
		'legalName'  => rw24_contact( 'rw_owner' ),
		'url'        => $home,
		'telephone'  => substr( rw24_phone_href(), 4 ),
		'email'      => rw24_contact( 'rw_email' ),
		'address'    => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => rw24_contact( 'rw_street' ),
			'postalCode'      => rw24_contact( 'rw_zip' ),
			'addressLocality' => rw24_contact( 'rw_city' ),
			'addressRegion'   => 'NRW',
			'addressCountry'  => 'DE',
		),
		'areaServed' => $area,
	);

	$logo = (int) get_theme_mod( 'custom_logo' );
	if ( $logo ) {
		$org['logo'] = wp_get_attachment_image_url( $logo, 'full' );
	}

	if ( rw24_share_image() ) {
		$org['image'] = rw24_share_image();
	}

	$graph[] = $org;

	$graph[] = array(
		'@type'     => 'WebSite',
		'@id'       => $home . '#website',
		'url'       => $home,
		'name'      => get_bloginfo( 'name' ),
		'publisher' => array( '@id' => $business ),
		'inLanguage' => get_bloginfo( 'language' ),
	);

	// Leistung, ggf. auf eine Stadt bezogen.
	if ( is_singular( 'rw_service' ) ) {
		$id      = get_queried_object_id();
		$city    = rw24_current_city();
		$service = array(
			'@type'       => 'Service',
			'name'        => $city ? get_the_title( $id ) . ' in ' . $city['name'] : get_the_title( $id ),
			'serviceType' => get_the_title( $id ),
			'description' => rw24_meta_description(),
			'provider'    => array( '@id' => $business ),
			'url'         => $city ? rw24_city_canonical() : get_permalink( $id ),
			'areaServed'  => $city
				? array( '@type' => 'City', 'name' => $city['name'], 'postalCode' => $city['zip'] )
				: array( '@type' => 'State', 'name' => 'Nordrhein-Westfalen' ),
		);

		$image = rw24_service_image( $id, 'hero' );
		if ( $image ) {
			$service['image'] = wp_get_attachment_image_url( $image, 'rw24-service-hero' );
		}

		$graph[] = $service;

		$faq = rw24_service_faq( $id );
		if ( $faq ) {
			$graph[] = array(
				'@type'      => 'FAQPage',
				'mainEntity' => array_map(
					static fn( array $item ): array => array(
						'@type'          => 'Question',
						'name'           => $item['q'],
						'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $item['a'] ),
					),
					$faq
				),
			);
		}
	}

	// Breadcrumbs - bei aktivem SEO-Plugin liefert das Plugin sie selbst.
	$trail = rw24_breadcrumb_trail();
	if ( ! rw24_seo_plugin_active() && count( $trail ) > 1 ) {
		$items = array();
		foreach ( array_values( $trail ) as $i => $item ) {
			$entry = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $item['label'],
			);
			if ( $item['url'] ) {
				$entry['item'] = $item['url'];
			}
			$items[] = $entry;
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => apply_filters( 'rw24_schema_graph', $graph ),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}
add_action( 'wp_head', 'rw24_schema', 20 );

/**
 * Robots: Suchergebnisse und Danke-Ansichten nicht indexieren.
 *
 * @param array $robots Robots-Direktiven.
 */
function rw24_robots( array $robots ): array {
	$thanks = is_page( 'anfrage' ) && isset( $_GET['anfrage'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( is_search() || $thanks ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	} elseif ( ! rw24_seo_plugin_active() && ! is_404() ) {
		$robots['max-image-preview'] = 'large';
	}

	return $robots;
}
add_filter( 'wp_robots', 'rw24_robots' );
