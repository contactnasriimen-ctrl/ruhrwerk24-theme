<?php
/**
 * Ersteinrichtung beim Aktivieren des Themes.
 *
 * Legt Gruppen, Leistungen, Pflichtseiten und Menüs an und setzt die
 * Startseite. Jeder Schritt prüft, ob es den Eintrag schon gibt - ein
 * erneutes Aktivieren überschreibt also nichts, was die Redaktion
 * geändert hat.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pflichtseiten.
 *
 * @return array<string, array{title: string, template?: string, content?: string, excerpt?: string}>
 */
function rw24_blueprint_pages(): array {
	return array(
		'startseite'                => array( 'title' => __( 'Startseite', 'ruhrwerk24' ) ),
		'anfrage'                   => array( 'title' => __( 'Anfrage in 60 Sekunden', 'ruhrwerk24' ) ),
		'notdienst'                 => array( 'title' => __( 'Notdienst', 'ruhrwerk24' ) ),
		'servicegebiete-uebersicht' => array(
			'title'    => __( 'Servicegebiete', 'ruhrwerk24' ),
			'template' => 'page-servicegebiete.php',
		),
		'impressum'                 => array(
			'title'   => __( 'Impressum', 'ruhrwerk24' ),
			'content' => '<!-- wp:paragraph --><p>' . __( 'Bitte ergänzen: Angaben gemäß § 5 DDG (Name, Anschrift, Vertretungsberechtigte, Kontakt, Registereintrag, USt-ID, Kammer und Berufsbezeichnung).', 'ruhrwerk24' ) . '</p><!-- /wp:paragraph -->',
		),
		'datenschutz'               => array(
			'title'   => __( 'Datenschutzerklärung', 'ruhrwerk24' ),
			'content' => '<!-- wp:paragraph --><p>' . __( 'Bitte ergänzen. Hinweise für die Erklärung: Das Theme lädt keine externen Schriften oder Skripte und setzt keine Cookies. Anfragen über das Formular werden in WordPress gespeichert und nach der im Customizer eingestellten Frist automatisch gelöscht.', 'ruhrwerk24' ) . '</p><!-- /wp:paragraph -->',
		),
	);
}

/**
 * Seite anlegen, falls es den Slug noch nicht gibt.
 *
 * @return int Seiten-ID.
 */
function rw24_ensure_page( string $slug, array $page ): int {
	$existing = get_page_by_path( $slug );

	if ( $existing ) {
		return (int) $existing->ID;
	}

	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $page['title'],
			'post_content' => $page['content'] ?? '',
			'post_excerpt' => $page['excerpt'] ?? '',
		)
	);

	if ( $id && ! is_wp_error( $id ) && ! empty( $page['template'] ) ) {
		update_post_meta( $id, '_wp_page_template', $page['template'] );
	}

	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Gruppen und Leistungen anlegen.
 */
function rw24_setup_services(): void {
	$order = 0;
	$terms = array();

	foreach ( rw24_blueprint_groups() as $slug => $group ) {
		$term = term_exists( $slug, 'rw_service_group' );

		if ( ! $term ) {
			$term = wp_insert_term( $group['name'], 'rw_service_group', array( 'slug' => $slug, 'description' => $group['description'] ) );
		}

		if ( is_wp_error( $term ) ) {
			continue;
		}

		$terms[ $slug ] = (int) $term['term_id'];

		// metadata_exists statt Leerprüfung: register_term_meta liefert sonst den Standardwert 0.
		if ( ! metadata_exists( 'term', $terms[ $slug ], '_rw_order' ) ) {
			update_term_meta( $terms[ $slug ], '_rw_order', $order );
		}

		$order += 10;
	}

	$menu_order = 0;

	foreach ( rw24_blueprint_services() as $slug => $service ) {
		$menu_order += 10;

		if ( get_page_by_path( $slug, OBJECT, 'rw_service' ) ) {
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'rw_service',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => $service['title'],
				'post_excerpt' => $service['excerpt'],
				'post_content' => $service['content'],
				'menu_order'   => $menu_order,
			)
		);

		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}

		if ( isset( $terms[ $service['group'] ] ) ) {
			wp_set_object_terms( $id, $terms[ $service['group'] ], 'rw_service_group' );
		}

		$faq = array();
		foreach ( $service['faq'] as $q => $a ) {
			$faq[] = $q . ' | ' . $a;
		}

		update_post_meta( $id, '_rw_short_claim', $service['claim'] );
		update_post_meta( $id, '_rw_emergency', $service['emergency'] ? '1' : '' );
		update_post_meta( $id, '_rw_icon', $service['icon'] );
		update_post_meta( $id, '_rw_features', implode( "\n", $service['features'] ) );
		update_post_meta( $id, '_rw_faq', implode( "\n", $faq ) );
		rw24_store_blueprint_hash( $id );
	}

	rw24_flush_service_cache();
}

/**
 * Prüfsumme der Texte, wie das Theme sie angelegt hat.
 *
 * Solange sie zum aktuellen Inhalt passt, hat niemand den Text bearbeitet
 * und ein Theme-Update darf ihn durch die neue Fassung ersetzen.
 */
function rw24_blueprint_fingerprint( int $id ): string {
	return md5( get_post_field( 'post_content', $id ) . '|' . get_post_meta( $id, '_rw_features', true ) . '|' . get_post_meta( $id, '_rw_faq', true ) );
}

function rw24_store_blueprint_hash( int $id ): void {
	update_post_meta( $id, '_rw_blueprint_hash', rw24_blueprint_fingerprint( $id ) );
}

/**
 * Nach einem Theme-Update unveränderte Leistungstexte auffrischen.
 *
 * Läuft einmal pro Version im Backend. Bearbeitete Texte bleiben
 * unangetastet - ebenso Leistungen ohne Prüfsumme (vor Version 2.2 angelegt).
 */
function rw24_upgrade_blueprint(): void {
	if ( get_option( 'rw24_setup_version' ) === RW24_VERSION || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	foreach ( rw24_blueprint_services() as $slug => $service ) {
		$post = get_page_by_path( $slug, OBJECT, 'rw_service' );
		$hash = $post ? (string) get_post_meta( $post->ID, '_rw_blueprint_hash', true ) : '';

		if ( ! $post || ! $hash || rw24_blueprint_fingerprint( $post->ID ) !== $hash ) {
			continue;
		}

		$faq = array();
		foreach ( $service['faq'] as $q => $a ) {
			$faq[] = $q . ' | ' . $a;
		}

		wp_update_post( array( 'ID' => $post->ID, 'post_content' => $service['content'] ) );
		update_post_meta( $post->ID, '_rw_features', implode( "\n", $service['features'] ) );
		update_post_meta( $post->ID, '_rw_faq', implode( "\n", $faq ) );
		rw24_store_blueprint_hash( $post->ID );
	}

	rw24_flush_service_cache();

	// Nur bei bereits eingerichteten Seiten weiterzählen - sonst zeigt der
	// Einrichtungs-Hinweis nicht mehr an.
	if ( get_option( 'rw24_setup_version' ) ) {
		update_option( 'rw24_setup_version', RW24_VERSION );
	}
}
add_action( 'admin_init', 'rw24_upgrade_blueprint' );

/**
 * Menü anlegen und einem Ort zuweisen, wenn dort noch keins sitzt.
 *
 * @param string $location Menü-Ort.
 * @param string $name     Name des Menüs.
 * @param array  $items    Einträge mit 'title' und 'page' (ID), 'archive' (Post-Type) oder 'url'.
 */
function rw24_ensure_menu( string $location, string $name, array $items ): void {
	$locations = get_nav_menu_locations();

	if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
		return;
	}

	$menu    = wp_get_nav_menu_object( $name );
	$menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $name );

	if ( ! $menu_id ) {
		return;
	}

	if ( ! $menu ) {
		foreach ( $items as $item ) {
			$args = array(
				'menu-item-title'  => $item['title'],
				'menu-item-status' => 'publish',
			);

			if ( ! empty( $item['archive'] ) ) {
				// Archiv-Eintrag statt fester URL - überlebt einen Domainwechsel.
				$args['menu-item-object'] = $item['archive'];
				$args['menu-item-type']   = 'post_type_archive';
			} elseif ( ! empty( $item['page'] ) ) {
				$args['menu-item-object']    = 'page';
				$args['menu-item-object-id'] = $item['page'];
				$args['menu-item-type']      = 'post_type';
			} else {
				$args['menu-item-url']  = $item['url'];
				$args['menu-item-type'] = 'custom';
			}

			wp_update_nav_menu_item( $menu_id, 0, $args );
		}
	}

	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Komplette Ersteinrichtung.
 */
function rw24_setup_content(): void {
	// Inhaltstypen müssen registriert sein, bevor Einträge angelegt werden.
	rw24_register_content_types();
	rw24_register_leads();

	// Sprechende Adressen sind Voraussetzung für die Stadtseiten - und
	// müssen stehen, bevor die Menüs Links auf Archive speichern.
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		$GLOBALS['wp_rewrite']->init();
	}

	rw24_setup_services();

	$ids = array();
	foreach ( rw24_blueprint_pages() as $slug => $page ) {
		$ids[ $slug ] = rw24_ensure_page( $slug, $page );
	}

	// Statische Startseite nur setzen, wenn bisher die Beitragsliste dort steht.
	if ( 'posts' === get_option( 'show_on_front' ) && $ids['startseite'] ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['startseite'] );
	}

	// WordPress legt bei der Installation einen Entwurf an. Solange der nicht
	// veröffentlicht ist, verlinkt das Formular unsere Seite.
	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $ids['datenschutz'] && ( ! $privacy || 'publish' !== get_post_status( $privacy ) ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['datenschutz'] );
	}

	rw24_ensure_menu(
		'primary',
		__( 'Hauptnavigation', 'ruhrwerk24' ),
		array(
			array( 'title' => __( 'Leistungen', 'ruhrwerk24' ), 'archive' => 'rw_service' ),
			array( 'title' => __( 'Notdienst', 'ruhrwerk24' ), 'page' => $ids['notdienst'] ),
			array( 'title' => __( 'Servicegebiete', 'ruhrwerk24' ), 'page' => $ids['servicegebiete-uebersicht'] ),
		)
	);

	rw24_ensure_menu(
		'legal',
		__( 'Rechtliches', 'ruhrwerk24' ),
		array(
			array( 'title' => __( 'Impressum', 'ruhrwerk24' ), 'page' => $ids['impressum'] ),
			array( 'title' => __( 'Datenschutz', 'ruhrwerk24' ), 'page' => $ids['datenschutz'] ),
		)
	);

	rw24_city_rewrites();
	flush_rewrite_rules( false );
	update_option( 'rw24_setup_version', RW24_VERSION );
}
add_action( 'after_switch_theme', 'rw24_setup_content' );

/**
 * Hinweis im Backend, falls die Einrichtung noch nie gelaufen ist
 * (z. B. Theme per FTP hochgeladen und direkt in der Datenbank aktiviert).
 */
function rw24_setup_notice(): void {
	if ( get_option( 'rw24_setup_version' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$url = wp_nonce_url( admin_url( 'admin-post.php?action=rw24_setup' ), 'rw24_setup' );

	printf(
		'<div class="notice notice-info"><p><strong>Ruhrwerk24:</strong> %s <a class="button button-primary" href="%s">%s</a></p></div>',
		esc_html__( 'Leistungen, Seiten und Menüs sind noch nicht angelegt.', 'ruhrwerk24' ),
		esc_url( $url ),
		esc_html__( 'Jetzt einrichten', 'ruhrwerk24' )
	);
}
add_action( 'admin_notices', 'rw24_setup_notice' );

/**
 * Einrichtung per Klick auf den Hinweis.
 */
function rw24_setup_action(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Keine Berechtigung.', 'ruhrwerk24' ), 403 );
	}

	check_admin_referer( 'rw24_setup' );
	rw24_setup_content();

	wp_safe_redirect( admin_url( 'edit.php?post_type=rw_service' ) );
	exit;
}
add_action( 'admin_post_rw24_setup', 'rw24_setup_action' );
