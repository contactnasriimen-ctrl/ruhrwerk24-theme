<?php
/**
 * Meta-Box "Details" für Leistungen.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta-Felder registrieren, damit sie typisiert und im REST verfügbar sind.
 */
function rw24_register_service_meta(): void {
	$fields = array(
		'_rw_short_claim' => array( 'string', 'sanitize_text_field' ),
		'_rw_emergency'   => array( 'string', 'rw24_sanitize_flag' ),
		'_rw_icon'        => array( 'string', 'sanitize_key' ),
		'_rw_features'    => array( 'string', 'sanitize_textarea_field' ),
		'_rw_faq'         => array( 'string', 'sanitize_textarea_field' ),
		'_rw_hashtags'    => array( 'string', 'sanitize_text_field' ),
	);

	foreach ( $fields as $key => [ $type, $sanitize ] ) {
		register_post_meta(
			'rw_service',
			$key,
			array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => $sanitize,
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);
	}
}
add_action( 'init', 'rw24_register_service_meta' );

/**
 * Checkbox-Wert normalisieren: "1" oder leer.
 *
 * @param mixed $value Rohwert.
 */
function rw24_sanitize_flag( $value ): string {
	return $value ? '1' : '';
}

/**
 * Meta-Box anmelden.
 */
function rw24_add_service_box(): void {
	add_meta_box( 'rw24-service', __( 'Details der Leistung', 'ruhrwerk24' ), 'rw24_render_service_box', 'rw_service', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'rw24_add_service_box' );

/**
 * Meta-Box ausgeben.
 */
function rw24_render_service_box( WP_Post $post ): void {
	wp_nonce_field( 'rw24_service_save', 'rw24_service_nonce' );

	$claim     = (string) get_post_meta( $post->ID, '_rw_short_claim', true );
	$emergency = (string) get_post_meta( $post->ID, '_rw_emergency', true );
	$icon      = rw24_service_icon( $post->ID );
	$features  = (string) get_post_meta( $post->ID, '_rw_features', true );
	$faq       = (string) get_post_meta( $post->ID, '_rw_faq', true );
	$hashtags  = (string) get_post_meta( $post->ID, '_rw_hashtags', true );
	?>
	<style>
		.rw24-box p { margin: 0 0 1.25rem; }
		.rw24-box label { display: block; font-weight: 600; margin-bottom: .35rem; }
		.rw24-box input[type=text], .rw24-box textarea, .rw24-box select { width: 100%; }
		.rw24-box .description { margin-top: .35rem; }
		.rw24-box .rw24-inline { display: flex; gap: .5rem; align-items: center; font-weight: 600; }
	</style>
	<div class="rw24-box">
		<p>
			<label for="rw-claim"><?php esc_html_e( 'Kurzversprechen', 'ruhrwerk24' ); ?></label>
			<input type="text" id="rw-claim" name="rw_short_claim" value="<?php echo esc_attr( $claim ); ?>" maxlength="160">
			<span class="description"><?php esc_html_e( 'Ein Satz unter der Überschrift. Wird auch als Google-Beschreibung genutzt (max. 160 Zeichen).', 'ruhrwerk24' ); ?></span>
		</p>

		<p>
			<label class="rw24-inline">
				<input type="checkbox" name="rw_emergency" value="1" <?php checked( '1', $emergency ); ?>>
				<?php esc_html_e( 'Im Notdienst verfügbar', 'ruhrwerk24' ); ?>
			</label>
			<span class="description"><?php esc_html_e( 'Erscheint dann auf der Notdienst-Seite und bekommt ein 24/7-Abzeichen.', 'ruhrwerk24' ); ?></span>
		</p>

		<p>
			<label for="rw-icon"><?php esc_html_e( 'Symbol', 'ruhrwerk24' ); ?></label>
			<select id="rw-icon" name="rw_icon">
				<?php foreach ( rw24_icon_choices() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $icon, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="rw-features"><?php esc_html_e( 'Leistungsmerkmale', 'ruhrwerk24' ); ?></label>
			<textarea id="rw-features" name="rw_features" rows="5"><?php echo esc_textarea( $features ); ?></textarea>
			<span class="description"><?php esc_html_e( 'Ein Merkmal pro Zeile. Erscheint als Häkchen-Liste neben dem Text.', 'ruhrwerk24' ); ?></span>
		</p>

		<p>
			<label for="rw-faq"><?php esc_html_e( 'Häufige Fragen', 'ruhrwerk24' ); ?></label>
			<textarea id="rw-faq" name="rw_faq" rows="6" placeholder="<?php esc_attr_e( 'Was kostet das? | Sie erhalten vor Arbeitsbeginn einen Festpreis.', 'ruhrwerk24' ); ?>"><?php echo esc_textarea( $faq ); ?></textarea>
			<span class="description"><?php esc_html_e( 'Eine Frage pro Zeile, Antwort nach einem senkrechten Strich ( | ). Wird auch für Google als FAQ ausgezeichnet.', 'ruhrwerk24' ); ?></span>
		</p>

		<p>
			<label for="rw-hashtags"><?php esc_html_e( 'Eigene Hashtags', 'ruhrwerk24' ); ?></label>
			<input type="text" id="rw-hashtags" name="rw_hashtags" value="<?php echo esc_attr( $hashtags ); ?>" placeholder="#Rohrbruch #Wasserschaden">
			<span class="description"><?php esc_html_e( 'Optional, mit Leerzeichen getrennt. Hashtags aus Leistung, Stadt und Customizer werden automatisch ergänzt.', 'ruhrwerk24' ); ?></span>
		</p>
	</div>
	<?php
}

/**
 * Meta-Box speichern.
 */
function rw24_save_service_box( int $post_id ): void {
	if ( ! isset( $_POST['rw24_service_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['rw24_service_nonce'] ), 'rw24_service_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text = static fn( string $key ): string => isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- register_post_meta sanitisiert.

	update_post_meta( $post_id, '_rw_short_claim', $text( 'rw_short_claim' ) );
	update_post_meta( $post_id, '_rw_emergency', isset( $_POST['rw_emergency'] ) ? '1' : '' );
	update_post_meta( $post_id, '_rw_features', $text( 'rw_features' ) );
	update_post_meta( $post_id, '_rw_faq', $text( 'rw_faq' ) );
	update_post_meta( $post_id, '_rw_hashtags', $text( 'rw_hashtags' ) );

	$icon = sanitize_key( $text( 'rw_icon' ) );
	update_post_meta( $post_id, '_rw_icon', array_key_exists( $icon, rw24_icon_choices() ) ? $icon : 'tool' );
}
add_action( 'save_post_rw_service', 'rw24_save_service_box' );
