<?php
/**
 * Cookie-Hinweis mit echter Wahl: Ablehnen ist genauso einfach wie
 * Akzeptieren, Kategorien lassen sich einzeln wählen, die Entscheidung
 * kann jederzeit geändert werden.
 *
 * Das Theme selbst setzt keine Cookies. Optionale Dienste (Statistik-Code
 * aus dem Customizer) stehen als <script type="text/plain"> im Quelltext
 * und werden von main.js erst nach Zustimmung ausgeführt.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ist der Hinweis eingeschaltet?
 */
function rw24_consent_enabled(): bool {
	return (bool) apply_filters( 'rw24_consent_enabled', (bool) rw24_contact( 'rw_consent_enabled' ) );
}

/**
 * Kategorien für den Einstellungsdialog.
 *
 * @return array<string, array{label: string, text: string, locked?: bool}>
 */
function rw24_consent_categories(): array {
	$stats = rw24_contact( 'rw_consent_stats_name' );

	return (array) apply_filters(
		'rw24_consent_categories',
		array(
			'necessary'  => array(
				'label'  => __( 'Notwendig', 'ruhrwerk24' ),
				'text'   => __( 'Speichert nur Ihre Auswahl in diesem Hinweis. Immer aktiv.', 'ruhrwerk24' ),
				'locked' => true,
			),
			'statistics' => array(
				'label' => __( 'Statistik', 'ruhrwerk24' ),
				'text'  => $stats
					/* translators: %s: Name des Dienstes. */
					? sprintf( __( 'Hilft uns zu verstehen, welche Seiten gelesen werden (%s).', 'ruhrwerk24' ), $stats )
					: __( 'Hilft uns zu verstehen, welche Seiten gelesen werden. Derzeit ist kein Dienst aktiv.', 'ruhrwerk24' ),
			),
			'marketing'  => array(
				'label' => __( 'Marketing', 'ruhrwerk24' ),
				'text'  => __( 'Für Werbung auf anderen Plattformen. Derzeit ist kein Dienst aktiv.', 'ruhrwerk24' ),
			),
		)
	);
}

/**
 * Hinweis und zurückgehaltene Scripts ausgeben.
 */
function rw24_consent_markup(): void {
	if ( ! rw24_consent_enabled() ) {
		return;
	}

	$privacy = get_privacy_policy_url();
	$code    = (string) get_theme_mod( 'rw_consent_stats_code', '' );

	// Optionaler Statistik-Code: nicht ausführbar, bis main.js ihn nach Zustimmung aktiviert.
	if ( '' !== trim( $code ) ) {
		echo '<template data-rw-consent="statistics">' . $code . '</template>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- nur mit unfiltered_html gespeichert.
	}
	?>
	<div class="consent" id="consent" role="dialog" aria-modal="false" aria-labelledby="consent-title" aria-describedby="consent-text" hidden>
		<div class="consent__card">
			<div class="consent__head">
				<span class="consent__icon" aria-hidden="true"><?php echo rw24_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<p class="consent__title" id="consent-title"><?php esc_html_e( 'Ihre Privatsphäre', 'ruhrwerk24' ); ?></p>
			</div>
			<p class="consent__text" id="consent-text">
				<?php esc_html_e( 'Wir verwenden nur technisch notwendige Speicherungen. Mit Ihrer Zustimmung nutzen wir zusätzlich Dienste für Statistik und Marketing. Sie können frei wählen und Ihre Entscheidung jederzeit ändern.', 'ruhrwerk24' ); ?>
				<?php if ( $privacy ) : ?>
					<a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'Datenschutzerklärung', 'ruhrwerk24' ); ?></a>
				<?php endif; ?>
			</p>

			<div class="consent__options" id="consent-options" hidden>
				<?php foreach ( rw24_consent_categories() as $rw_key => $rw_cat ) : ?>
					<label class="consent__option">
						<span>
							<strong><?php echo esc_html( $rw_cat['label'] ); ?></strong>
							<small><?php echo esc_html( $rw_cat['text'] ); ?></small>
						</span>
						<input type="checkbox" class="consent__switch" data-consent-cat="<?php echo esc_attr( $rw_key ); ?>"<?php echo ! empty( $rw_cat['locked'] ) ? ' checked disabled' : ''; ?>>
					</label>
				<?php endforeach; ?>
			</div>

			<div class="consent__actions">
				<button type="button" class="btn consent__btn consent__btn--reject" data-consent="reject"><?php esc_html_e( 'Ablehnen', 'ruhrwerk24' ); ?></button>
				<button type="button" class="btn consent__btn consent__btn--accept" data-consent="accept"><?php esc_html_e( 'Akzeptieren', 'ruhrwerk24' ); ?></button>
				<button type="button" class="consent__more" data-consent="settings" aria-expanded="false" aria-controls="consent-options"><?php esc_html_e( 'Einstellungen', 'ruhrwerk24' ); ?></button>
				<button type="button" class="btn consent__btn consent__btn--save" data-consent="save" hidden><?php esc_html_e( 'Auswahl speichern', 'ruhrwerk24' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'rw24_consent_markup', 5 );
