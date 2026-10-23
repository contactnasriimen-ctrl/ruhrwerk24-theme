<?php
/**
 * Stadtseiten in der XML-Sitemap von WordPress.
 *
 * Die Stadtseiten existieren nicht in der Datenbank, also kennt die
 * Core-Sitemap sie nicht. Dieser Provider ergänzt /wp-sitemap-rwcity-1.xml.
 * Anfragen (rw_lead) sind nicht öffentlich und tauchen ohnehin nicht auf.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Provider registrieren, sobald die Sitemap-Klassen geladen sind.
 */
function rw24_register_sitemap(): void {
	if ( ! class_exists( 'WP_Sitemaps_Provider' ) ) {
		return;
	}

	/**
	 * Sitemap-Provider für Stadt-Landingpages.
	 */
	final class RW24_City_Sitemap extends WP_Sitemaps_Provider {

		/**
		 * Einträge pro Sitemap-Datei. Google erlaubt 50.000.
		 */
		private const PER_PAGE = 2000;

		public function __construct() {
			$this->name        = 'rwcity';
			$this->object_type = 'rwcity';
		}

		/**
		 * Alle Adressen: erst die Stadtübersichten, dann Leistung × Stadt.
		 *
		 * @return string[]
		 */
		private function all_urls(): array {
			$urls = array();

			if ( rw24_areas_url() ) {
				foreach ( array_keys( rw24_cities() ) as $slug ) {
					$urls[] = rw24_city_hub_url( $slug );
				}
			}

			foreach ( rw24_all_services() as $service ) {
				foreach ( array_keys( rw24_cities() ) as $slug ) {
					$urls[] = rw24_city_url( $service, $slug );
				}
			}

			return $urls;
		}

		/**
		 * @param int    $page_num       Seite.
		 * @param string $object_subtype Nicht genutzt.
		 */
		public function get_url_list( $page_num, $object_subtype = '' ) {
			$chunk = array_slice( $this->all_urls(), ( max( 1, (int) $page_num ) - 1 ) * self::PER_PAGE, self::PER_PAGE );

			return array_map( static fn( string $loc ): array => array( 'loc' => $loc ), $chunk );
		}

		/**
		 * @param string $object_subtype Nicht genutzt.
		 */
		public function get_max_num_pages( $object_subtype = '' ) {
			return (int) ceil( count( $this->all_urls() ) / self::PER_PAGE );
		}
	}

	wp_register_sitemap_provider( 'rwcity', new RW24_City_Sitemap() );
}
add_action( 'init', 'rw24_register_sitemap', 20 );

/**
 * Taxonomie-Sitemap der Gruppen behalten, Nutzer-Sitemap entfernen.
 *
 * Autorenseiten hat dieses Theme nicht - die Sitemap würde auf leere
 * Archive zeigen.
 *
 * @param WP_Sitemaps_Provider $provider Provider.
 * @param string               $name     Name.
 */
function rw24_sitemap_providers( $provider, string $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'rw24_sitemap_providers', 10, 2 );

/**
 * Autorenarchive auf die Startseite leiten.
 *
 * Sie verraten Benutzernamen und haben in einem Handwerkerauftritt keinen
 * Inhalt.
 */
function rw24_no_author_archives(): void {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'rw24_no_author_archives' );
