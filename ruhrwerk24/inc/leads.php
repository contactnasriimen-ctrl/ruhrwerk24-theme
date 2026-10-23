<?php
/**
 * Anfragen (Leads): Speicherung, Backend-Liste, Status, Export, Löschfrist.
 *
 * Jede Anfrage wird gespeichert, bevor eine Mail rausgeht. Fällt der
 * Mailversand aus, geht trotzdem keine Anfrage verloren.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bearbeitungsstände einer Anfrage.
 *
 * @return array<string, string>
 */
function rw24_lead_statuses(): array {
	return array(
		'neu'         => __( 'Neu', 'ruhrwerk24' ),
		'kontaktiert' => __( 'Kontaktiert', 'ruhrwerk24' ),
		'angebot'     => __( 'Angebot verschickt', 'ruhrwerk24' ),
		'erledigt'    => __( 'Erledigt', 'ruhrwerk24' ),
	);
}

/**
 * Inhaltstyp registrieren - nur im Backend sichtbar.
 */
function rw24_register_leads(): void {
	register_post_type(
		'rw_lead',
		array(
			'labels'              => array(
				'name'          => __( 'Anfragen', 'ruhrwerk24' ),
				'singular_name' => __( 'Anfrage', 'ruhrwerk24' ),
				'edit_item'     => __( 'Anfrage', 'ruhrwerk24' ),
				'search_items'  => __( 'Anfragen durchsuchen', 'ruhrwerk24' ),
				'not_found'     => __( 'Noch keine Anfragen.', 'ruhrwerk24' ),
				'all_items'     => __( 'Alle Anfragen', 'ruhrwerk24' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 3,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		)
	);
}
add_action( 'init', 'rw24_register_leads' );

/**
 * Fortlaufende Anfragenummer: RW-JJMM-0001, je Monat neu gezählt.
 */
function rw24_next_lead_ref(): string {
	$period  = wp_date( 'ym' );
	$counter = (array) get_option( 'rw24_lead_counter', array() );
	$next    = ( ( $counter['period'] ?? '' ) === $period ) ? (int) $counter['n'] + 1 : 1;

	update_option( 'rw24_lead_counter', array( 'period' => $period, 'n' => $next ), false );

	return sprintf( 'RW-%s-%04d', $period, $next );
}

/**
 * Anfrage speichern.
 *
 * @param array $data Bereinigte Formulardaten.
 * @return array{id: int, ref: string}|null
 */
function rw24_create_lead( array $data ): ?array {
	$ref = rw24_next_lead_ref();

	$id = wp_insert_post(
		array(
			'post_type'   => 'rw_lead',
			'post_status' => 'private',
			'post_title'  => $ref . ' · ' . $data['name'],
		),
		true
	);

	if ( is_wp_error( $id ) ) {
		return null;
	}

	update_post_meta( $id, '_rw_ref', $ref );
	update_post_meta( $id, '_rw_status', 'neu' );
	update_post_meta( $id, '_rw_lead', $data );

	/**
	 * Nach dem Speichern einer Anfrage, z. B. für CRM-Anbindungen.
	 *
	 * @param int    $id   Post-ID der Anfrage.
	 * @param array  $data Formulardaten.
	 * @param string $ref  Anfragenummer.
	 */
	do_action( 'rw24_lead_created', $id, $data, $ref );

	return array(
		'id'  => (int) $id,
		'ref' => $ref,
	);
}

/**
 * Daten einer Anfrage.
 */
function rw24_lead_data( int $id ): array {
	$data = get_post_meta( $id, '_rw_lead', true );

	return is_array( $data ) ? $data : array();
}

/**
 * Lesbare Felder einer Anfrage, in der Reihenfolge des Formulars.
 *
 * Gemeinsame Quelle für Backend, Mails und CSV-Export.
 *
 * @return array<string, string>
 */
function rw24_lead_rows( int $id ): array {
	$d = rw24_lead_data( $id );

	return array(
		__( 'Anfragenummer', 'ruhrwerk24' ) => (string) get_post_meta( $id, '_rw_ref', true ),
		__( 'Leistung', 'ruhrwerk24' )      => rw24_choice_label( 'service', $d['service'] ?? '' ),
		__( 'Dringlichkeit', 'ruhrwerk24' ) => rw24_choice_label( 'urgency', $d['urgency'] ?? '' ),
		__( 'Objekt', 'ruhrwerk24' )        => rw24_choice_label( 'property', $d['property'] ?? '' ),
		__( 'Ort', 'ruhrwerk24' )           => trim( ( $d['zip'] ?? '' ) . ' ' . ( $d['place'] ?? '' ) ),
		__( 'Beschreibung', 'ruhrwerk24' )  => $d['message'] ?? '',
		__( 'Name', 'ruhrwerk24' )          => $d['name'] ?? '',
		__( 'Telefon', 'ruhrwerk24' )       => $d['phone'] ?? '',
		__( 'E-Mail', 'ruhrwerk24' )        => $d['email'] ?? '',
		__( 'Gesendet von', 'ruhrwerk24' )  => $d['source'] ?? '',
	);
}

/**
 * Spalten der Anfragenliste.
 *
 * @param array $columns Standardspalten.
 */
function rw24_lead_columns( array $columns ): array {
	return array(
		'cb'         => $columns['cb'],
		'title'      => __( 'Anfrage', 'ruhrwerk24' ),
		'rw_service' => __( 'Leistung', 'ruhrwerk24' ),
		'rw_urgency' => __( 'Dringlichkeit', 'ruhrwerk24' ),
		'rw_place'   => __( 'Ort', 'ruhrwerk24' ),
		'rw_contact' => __( 'Kontakt', 'ruhrwerk24' ),
		'rw_status'  => __( 'Status', 'ruhrwerk24' ),
		'date'       => __( 'Eingang', 'ruhrwerk24' ),
	);
}
add_filter( 'manage_rw_lead_posts_columns', 'rw24_lead_columns' );

/**
 * Spalteninhalte.
 */
function rw24_lead_column( string $column, int $id ): void {
	$d = rw24_lead_data( $id );

	switch ( $column ) {
		case 'rw_service':
			echo esc_html( rw24_choice_label( 'service', $d['service'] ?? '' ) );
			break;

		case 'rw_urgency':
			$urgent = 'notfall' === ( $d['urgency'] ?? '' );
			printf(
				'<span style="%s">%s</span>',
				$urgent ? 'color:#b32d2e;font-weight:600' : '',
				esc_html( rw24_choice_label( 'urgency', $d['urgency'] ?? '' ) )
			);
			break;

		case 'rw_place':
			echo esc_html( trim( ( $d['zip'] ?? '' ) . ' ' . ( $d['place'] ?? '' ) ) );
			break;

		case 'rw_contact':
			if ( ! empty( $d['phone'] ) ) {
				printf( '<a href="%s">%s</a><br>', esc_url( rw24_phone_href( $d['phone'] ) ), esc_html( $d['phone'] ) );
			}
			if ( ! empty( $d['email'] ) ) {
				printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $d['email'] ) );
			}
			break;

		case 'rw_status':
			$status = (string) get_post_meta( $id, '_rw_status', true );
			printf(
				'<span class="rw24-status rw24-status--%s">%s</span>',
				esc_attr( $status ),
				esc_html( rw24_lead_statuses()[ $status ] ?? $status )
			);
			break;
	}
}
add_action( 'manage_rw_lead_posts_custom_column', 'rw24_lead_column', 10, 2 );

/**
 * Status-Farben und Aufräumen der Bearbeiten-Ansicht.
 */
function rw24_lead_admin_css(): void {
	$screen = get_current_screen();

	if ( ! $screen || 'rw_lead' !== $screen->post_type ) {
		return;
	}
	?>
	<style>
		.rw24-status { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 12px; font-weight: 600; background: #f0f0f1; }
		.rw24-status--neu { background: #fde8dd; color: #a2400c; }
		.rw24-status--kontaktiert { background: #e5f0fb; color: #135e96; }
		.rw24-status--angebot { background: #fcf3d6; color: #7a5a00; }
		.rw24-status--erledigt { background: #e6f4ea; color: #1e6b34; }
		.post-type-rw_lead #titlediv, .post-type-rw_lead #minor-publishing, .post-type-rw_lead .page-title-action { display: none; }
		.rw24-lead th { width: 160px; text-align: left; vertical-align: top; padding: 8px 12px 8px 0; color: #50575e; }
		.rw24-lead td { padding: 8px 0; white-space: pre-line; }
		.rw24-lead tr + tr { border-top: 1px solid #f0f0f1; }
	</style>
	<?php
}
add_action( 'admin_head', 'rw24_lead_admin_css' );

/**
 * Detail- und Status-Box.
 */
function rw24_lead_boxes(): void {
	add_meta_box( 'rw24-lead', __( 'Angaben', 'ruhrwerk24' ), 'rw24_render_lead_box', 'rw_lead', 'normal', 'high' );
	add_meta_box( 'rw24-lead-status', __( 'Bearbeitung', 'ruhrwerk24' ), 'rw24_render_lead_status_box', 'rw_lead', 'side', 'high' );
}
add_action( 'add_meta_boxes_rw_lead', 'rw24_lead_boxes' );

/**
 * Angaben der Anfrage (nur lesend).
 */
function rw24_render_lead_box( WP_Post $post ): void {
	echo '<table class="rw24-lead">';

	foreach ( rw24_lead_rows( $post->ID ) as $label => $value ) {
		if ( '' === $value ) {
			continue;
		}

		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';

		if ( __( 'E-Mail', 'ruhrwerk24' ) === $label ) {
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $value ) );
		} elseif ( __( 'Telefon', 'ruhrwerk24' ) === $label ) {
			printf( '<a href="%s">%s</a>', esc_url( rw24_phone_href( $value ) ), esc_html( $value ) );
		} elseif ( __( 'Gesendet von', 'ruhrwerk24' ) === $label ) {
			printf( '<a href="%1$s" target="_blank" rel="noopener">%1$s</a>', esc_url( $value ) );
		} else {
			echo esc_html( $value );
		}

		echo '</td></tr>';
	}

	echo '</table>';
}

/**
 * Status und interne Notiz.
 */
function rw24_render_lead_status_box( WP_Post $post ): void {
	wp_nonce_field( 'rw24_lead_save', 'rw24_lead_nonce' );

	$status = (string) get_post_meta( $post->ID, '_rw_status', true );
	$note   = (string) get_post_meta( $post->ID, '_rw_note', true );
	?>
	<p>
		<label for="rw-status"><strong><?php esc_html_e( 'Status', 'ruhrwerk24' ); ?></strong></label><br>
		<select id="rw-status" name="rw_status" style="width:100%">
			<?php foreach ( rw24_lead_statuses() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="rw-note"><strong><?php esc_html_e( 'Interne Notiz', 'ruhrwerk24' ); ?></strong></label><br>
		<textarea id="rw-note" name="rw_note" rows="5" style="width:100%"><?php echo esc_textarea( $note ); ?></textarea>
	</p>
	<?php
}

/**
 * Status und Notiz speichern.
 */
function rw24_save_lead( int $post_id ): void {
	if ( ! isset( $_POST['rw24_lead_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['rw24_lead_nonce'] ), 'rw24_lead_save' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$status = isset( $_POST['rw_status'] ) ? sanitize_key( $_POST['rw_status'] ) : 'neu';

	if ( isset( rw24_lead_statuses()[ $status ] ) ) {
		update_post_meta( $post_id, '_rw_status', $status );
	}

	if ( isset( $_POST['rw_note'] ) ) {
		update_post_meta( $post_id, '_rw_note', sanitize_textarea_field( wp_unslash( $_POST['rw_note'] ) ) );
	}

	delete_transient( 'rw24_new_leads' );
}
add_action( 'save_post_rw_lead', 'rw24_save_lead' );

/**
 * Anfragen bleiben privat, auch wenn jemand im Backend auf "Aktualisieren" klickt.
 *
 * @param array $data Post-Daten vor dem Speichern.
 */
function rw24_keep_lead_private( array $data ): array {
	if ( 'rw_lead' === $data['post_type'] && ! in_array( $data['post_status'], array( 'trash', 'auto-draft' ), true ) ) {
		$data['post_status'] = 'private';
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'rw24_keep_lead_private' );

/**
 * Filter nach Status über der Liste.
 *
 * @param array $views Standard-Ansichten.
 */
function rw24_lead_views( array $views ): array {
	$current = isset( $_GET['rw_status'] ) ? sanitize_key( $_GET['rw_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	foreach ( rw24_lead_statuses() as $value => $label ) {
		$url             = add_query_arg( array( 'post_type' => 'rw_lead', 'rw_status' => $value ), admin_url( 'edit.php' ) );
		$views[ $value ] = sprintf( '<a href="%s"%s>%s</a>', esc_url( $url ), $current === $value ? ' class="current" aria-current="page"' : '', esc_html( $label ) );
	}

	$export          = wp_nonce_url( admin_url( 'admin-post.php?action=rw24_leads_export' ), 'rw24_leads_export' );
	$views['export'] = '<a href="' . esc_url( $export ) . '">' . esc_html__( 'Als CSV exportieren', 'ruhrwerk24' ) . '</a>';

	return $views;
}
add_filter( 'views_edit-rw_lead', 'rw24_lead_views' );

/**
 * Statusfilter auf die Liste anwenden.
 */
function rw24_lead_filter_query( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() || 'rw_lead' !== $query->get( 'post_type' ) ) {
		return;
	}

	$status = isset( $_GET['rw_status'] ) ? sanitize_key( $_GET['rw_status'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( isset( rw24_lead_statuses()[ $status ] ) ) {
		$query->set( 'meta_key', '_rw_status' );
		$query->set( 'meta_value', $status );
	}
}
add_action( 'pre_get_posts', 'rw24_lead_filter_query' );

/**
 * Anzahl neuer Anfragen, für die Zahl am Menüpunkt.
 */
function rw24_new_lead_count(): int {
	$count = get_transient( 'rw24_new_leads' );

	if ( false === $count ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'rw_lead',
				'post_status'    => 'private',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_rw_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => 'neu', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		$count = (int) $query->found_posts;
		set_transient( 'rw24_new_leads', $count, 5 * MINUTE_IN_SECONDS );
	}

	return (int) $count;
}
add_action( 'rw24_lead_created', static fn() => delete_transient( 'rw24_new_leads' ) );

/**
 * Zahl neuer Anfragen am Menüpunkt anzeigen.
 */
function rw24_lead_menu_bubble(): void {
	global $menu;

	$count = rw24_new_lead_count();

	if ( ! $count || ! is_array( $menu ) ) {
		return;
	}

	foreach ( $menu as $i => $item ) {
		if ( 'edit.php?post_type=rw_lead' === ( $item[2] ?? '' ) ) {
			$menu[ $i ][0] .= sprintf( ' <span class="awaiting-mod"><span class="pending-count">%d</span></span>', $count ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			break;
		}
	}
}
add_action( 'admin_menu', 'rw24_lead_menu_bubble', 99 );

/**
 * CSV-Export aller Anfragen (Excel-tauglich: Semikolon, UTF-8 mit BOM).
 */
function rw24_export_leads(): void {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'Keine Berechtigung.', 'ruhrwerk24' ), 403 );
	}

	check_admin_referer( 'rw24_leads_export' );

	$ids = get_posts(
		array(
			'post_type'      => 'rw_lead',
			'post_status'    => 'private',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=anfragen-' . wp_date( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

	$header = false;

	foreach ( $ids as $id ) {
		$rows = rw24_lead_rows( $id );
		$rows = array( __( 'Eingang', 'ruhrwerk24' ) => get_the_date( 'd.m.Y H:i', $id ) ) + $rows + array(
			__( 'Status', 'ruhrwerk24' ) => rw24_lead_statuses()[ (string) get_post_meta( $id, '_rw_status', true ) ] ?? '',
		);

		if ( ! $header ) {
			fputcsv( $out, array_keys( $rows ), ';' );
			$header = true;
		}

		// Formeln in Excel verhindern (CSV-Injection).
		$values = array_map( static fn( string $v ): string => preg_match( '/^[=+\-@]/', $v ) ? "'" . $v : $v, array_map( 'strval', array_values( $rows ) ) );

		fputcsv( $out, $values, ';' );
	}

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_rw24_leads_export', 'rw24_export_leads' );

/**
 * Tägliche Löschung alter Anfragen planen.
 */
function rw24_schedule_lead_cleanup(): void {
	if ( ! wp_next_scheduled( 'rw24_lead_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'rw24_lead_cleanup' );
	}
}
add_action( 'init', 'rw24_schedule_lead_cleanup' );

/**
 * Anfragen löschen, die älter als die Frist im Customizer sind.
 */
function rw24_lead_cleanup(): void {
	$months = (int) rw24_contact( 'rw_lead_retention' );

	if ( $months < 1 ) {
		return;
	}

	$ids = get_posts(
		array(
			'post_type'      => 'rw_lead',
			'post_status'    => 'any',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'date_query'     => array( array( 'before' => $months . ' months ago' ) ),
		)
	);

	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
}
add_action( 'rw24_lead_cleanup', 'rw24_lead_cleanup' );

/**
 * Beim Theme-Wechsel den Cron-Job entfernen.
 */
function rw24_unschedule_lead_cleanup(): void {
	wp_clear_scheduled_hook( 'rw24_lead_cleanup' );
}
add_action( 'switch_theme', 'rw24_unschedule_lead_cleanup' );
