<?php
/**
 * Inhaltstypen: Leistungen, Leistungsgruppen, Anfragen.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Leistungen und Gruppen registrieren.
 *
 * Anfragen (rw_lead) registriert leads.php - sie sind nie öffentlich.
 */
function rw24_register_content_types(): void {
	register_post_type(
		'rw_service',
		array(
			'labels'        => array(
				'name'               => __( 'Leistungen', 'ruhrwerk24' ),
				'singular_name'      => __( 'Leistung', 'ruhrwerk24' ),
				'add_new'            => __( 'Neue Leistung', 'ruhrwerk24' ),
				'add_new_item'       => __( 'Neue Leistung anlegen', 'ruhrwerk24' ),
				'edit_item'          => __( 'Leistung bearbeiten', 'ruhrwerk24' ),
				'view_item'          => __( 'Leistung ansehen', 'ruhrwerk24' ),
				'all_items'          => __( 'Alle Leistungen', 'ruhrwerk24' ),
				'search_items'       => __( 'Leistungen durchsuchen', 'ruhrwerk24' ),
				'not_found'          => __( 'Keine Leistungen gefunden.', 'ruhrwerk24' ),
				'featured_image'     => __( 'Foto der Leistung', 'ruhrwerk24' ),
				'set_featured_image' => __( 'Foto festlegen (ideal 1920×1080)', 'ruhrwerk24' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-hammer',
			'menu_position' => 20,
			'has_archive'   => 'leistungen',
			'rewrite'       => array( 'slug' => 'leistungen', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
		)
	);

	register_taxonomy(
		'rw_service_group',
		'rw_service',
		array(
			'labels'            => array(
				'name'          => __( 'Leistungsgruppen', 'ruhrwerk24' ),
				'singular_name' => __( 'Leistungsgruppe', 'ruhrwerk24' ),
				'add_new_item'  => __( 'Neue Gruppe anlegen', 'ruhrwerk24' ),
				'edit_item'     => __( 'Gruppe bearbeiten', 'ruhrwerk24' ),
			),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'leistungsbereich', 'with_front' => false ),
		)
	);

	// Reihenfolge der Gruppen (term meta), damit sie sich ohne Plugin sortieren lassen.
	register_term_meta(
		'rw_service_group',
		'_rw_order',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
}
add_action( 'init', 'rw24_register_content_types' );

/**
 * Archiv und Gruppenseiten nach der Reihenfolge im Backend sortieren.
 */
function rw24_service_query_order( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'rw_service' ) || $query->is_tax( 'rw_service_group' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$query->set( 'posts_per_page', -1 );
	}
}
add_action( 'pre_get_posts', 'rw24_service_query_order' );

/**
 * Leistungen haben keine Kommentare.
 */
function rw24_no_service_comments( bool $open, int $post_id ): bool {
	return 'rw_service' === get_post_type( $post_id ) ? false : $open;
}
add_filter( 'comments_open', 'rw24_no_service_comments', 10, 2 );

/**
 * Feld "Reihenfolge" im Gruppen-Formular.
 */
function rw24_group_order_field( $term = null ): void {
	$value = $term instanceof WP_Term ? (int) get_term_meta( $term->term_id, '_rw_order', true ) : 0;
	?>
	<tr class="form-field">
		<th scope="row"><label for="rw-order"><?php esc_html_e( 'Reihenfolge', 'ruhrwerk24' ); ?></label></th>
		<td>
			<input type="number" min="0" step="1" name="rw_order" id="rw-order" value="<?php echo esc_attr( (string) $value ); ?>" style="width:6rem">
			<p class="description"><?php esc_html_e( 'Kleinere Zahlen stehen auf der Website weiter oben.', 'ruhrwerk24' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'rw_service_group_edit_form_fields', 'rw24_group_order_field' );

/**
 * Feld beim Anlegen einer Gruppe (dort ohne Tabellenlayout).
 */
function rw24_group_order_field_add(): void {
	?>
	<div class="form-field">
		<label for="rw-order"><?php esc_html_e( 'Reihenfolge', 'ruhrwerk24' ); ?></label>
		<input type="number" min="0" step="1" name="rw_order" id="rw-order" value="0">
	</div>
	<?php
}
add_action( 'rw_service_group_add_form_fields', 'rw24_group_order_field_add' );

/**
 * Reihenfolge speichern.
 *
 * Das Nonce prüft WordPress im Term-Formular bereits selbst.
 */
function rw24_group_order_save( int $term_id ): void {
	if ( ! isset( $_POST['rw_order'] ) || ! current_user_can( 'manage_categories' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	update_term_meta( $term_id, '_rw_order', absint( $_POST['rw_order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	rw24_flush_service_cache();
}
add_action( 'created_rw_service_group', 'rw24_group_order_save' );
add_action( 'edited_rw_service_group', 'rw24_group_order_save' );
