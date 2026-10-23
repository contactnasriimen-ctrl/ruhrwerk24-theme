<?php
/**
 * Anfrageformular: acht kurze Fragen, eine pro Schritt.
 *
 * Funktioniert auch ohne JavaScript - dann stehen alle Fragen
 * untereinander. main.js macht daraus den Schritt-für-Schritt-Ablauf.
 *
 * Spamschutz ohne Captcha und ohne Drittanbieter:
 *   - Honeypot-Feld, das Menschen nicht sehen
 *   - signierter Zeitstempel (zu schnell oder zu alt = Bot oder Replay)
 *   - Begrenzung auf wenige Anfragen pro Stunde und IP
 * Bewusst kein Nonce: Seiten-Caches würden ihn einfrieren und echte
 * Anfragen nach zwölf Stunden ablehnen.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Antwortmöglichkeiten der Auswahlfragen.
 *
 * @return array<string, array<string, string>>
 */
function rw24_form_choices(): array {
	$services = array();

	foreach ( rw24_all_services() as $service ) {
		$services[ $service->post_name ] = get_the_title( $service );
	}

	$services['sonstiges'] = __( 'Etwas anderes', 'ruhrwerk24' );

	return array(
		'service'  => $services,
		'urgency'  => array(
			'notfall'  => __( 'Notfall – sofort', 'ruhrwerk24' ),
			'woche'    => __( 'In den nächsten Tagen', 'ruhrwerk24' ),
			'monat'    => __( 'In den nächsten Wochen', 'ruhrwerk24' ),
			'flexibel' => __( 'Flexibel – erst einmal ein Angebot', 'ruhrwerk24' ),
		),
		'property' => array(
			'efh'     => __( 'Einfamilienhaus', 'ruhrwerk24' ),
			'mfh'     => __( 'Mehrfamilienhaus', 'ruhrwerk24' ),
			'wohnung' => __( 'Wohnung', 'ruhrwerk24' ),
			'gewerbe' => __( 'Gewerbe', 'ruhrwerk24' ),
		),
	);
}

/**
 * Bezeichnung einer Auswahl, z. B. für Mails und Backend.
 */
function rw24_choice_label( string $field, string $value ): string {
	return rw24_form_choices()[ $field ][ $value ] ?? $value;
}

/**
 * Signierter Zeitstempel für den Spamschutz.
 */
function rw24_form_token(): string {
	$time = (string) time();

	return $time . '.' . hash_hmac( 'sha256', $time, wp_salt( 'nonce' ) );
}

/**
 * Zeitstempel prüfen: mindestens 3 Sekunden, höchstens 7 Tage alt.
 */
function rw24_form_token_valid( string $token ): bool {
	$parts = explode( '.', $token, 2 );

	if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
		return false;
	}

	if ( ! hash_equals( hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) ), $parts[1] ) ) {
		return false;
	}

	$age = time() - (int) $parts[0];

	return $age >= 3 && $age <= WEEK_IN_SECONDS;
}

/**
 * Adresse der Anfrageseite.
 *
 * @param array $args Query-Parameter.
 */
function rw24_form_page_url( array $args = array() ): string {
	$page = get_page_by_path( 'anfrage' );
	$url  = $page ? (string) get_permalink( $page ) : home_url( '/anfrage/' );

	return $args ? add_query_arg( $args, $url ) : $url;
}

/**
 * Wert aus einem fehlgeschlagenen Versand, damit nichts neu getippt werden muss.
 */
function rw24_form_old( string $field ): string {
	static $old = null;

	if ( null === $old ) {
		$key = isset( $_GET['f'] ) ? sanitize_key( wp_unslash( $_GET['f'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$old = $key ? (array) get_transient( 'rw24_form_' . $key ) : array();
	}

	return isset( $old[ $field ] ) ? (string) $old[ $field ] : '';
}

/**
 * Formular ausgeben.
 *
 * @param string $context hero (kompakt, im Seitenkopf) oder page (Anfrageseite).
 */
function rw24_render_form( string $context = 'page' ): void {
	static $instance = 0;
	$instance++;

	$uid     = 'qf' . $instance;
	$choices = rw24_form_choices();
	$total   = 8;

	// Auf Leistungs- und Stadtseiten ist die Leistung schon klar.
	$preset_service = rw24_form_old( 'service' );
	if ( ! $preset_service && is_singular( 'rw_service' ) ) {
		$preset_service = get_post_field( 'post_name', get_queried_object_id() );
	}

	// Links wie /anfrage/?leistung=umzug wählen die Leistung schon vor.
	$requested = isset( $_GET['leistung'] ) ? sanitize_key( wp_unslash( $_GET['leistung'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $preset_service && isset( $choices['service'][ $requested ] ) ) {
		$preset_service = $requested;
	}

	$city        = rw24_current_city();
	$preset_zip  = rw24_form_old( 'zip' );
	$preset_city = $city ? $city['name'] : rw24_form_old( 'place' );

	$source = rw24_city_canonical();
	if ( ! $source ) {
		$source = is_singular() ? (string) get_permalink() : home_url( '/' );
	}

	$step = 0;

	// Kleine Helfer für die Fragen-Überschrift.
	$legend = static function ( string $question, string $hint = '' ) use ( &$step, $total ): void {
		$step++;
		printf(
			'<legend class="qf__q"><span class="qf__n">%1$s</span> %2$s</legend>%3$s',
			/* translators: 1: aktuelle Frage, 2: Anzahl Fragen. */
			esc_html( sprintf( __( 'Frage %1$d von %2$d', 'ruhrwerk24' ), $step, $total ) ),
			esc_html( $question ),
			$hint ? '<p class="qf__hint">' . esc_html( $hint ) . '</p>' : ''
		);
	};

	$radios = static function ( string $name, array $options, string $checked = '', bool $icons = false ) use ( $uid ): void {
		echo '<div class="qf__options' . ( $icons ? ' qf__options--icons' : '' ) . '">';

		$first = true;
		foreach ( $options as $value => $label ) {
			$id   = $uid . '-' . $name . '-' . $value;
			$icon = '';

			if ( $icons ) {
				$post = get_page_by_path( $value, OBJECT, 'rw_service' );
				$icon = rw24_icon( $post ? rw24_service_icon( $post->ID ) : 'chat' );
			}

			printf(
				'<label class="qf__option" for="%1$s"><input type="radio" id="%1$s" name="%2$s" value="%3$s"%4$s%5$s><span class="qf__card">%6$s<span>%7$s</span></span></label>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				checked( $checked, $value, false ),
				$first ? ' required' : '',
				$icon, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- statisches SVG.
				esc_html( $label )
			);
			$first = false;
		}

		echo '</div>';
	};
	?>
	<form class="qf qf--<?php echo esc_attr( $context ); ?>" id="<?php echo esc_attr( $uid ); ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" data-qf data-total="<?php echo esc_attr( (string) $total ); ?>">

		<?php if ( 'hero' === $context ) : ?>
			<div class="qf__head">
				<p class="qf__title"><?php esc_html_e( 'Anfrage in 60 Sekunden', 'ruhrwerk24' ); ?></p>
				<p class="qf__sub"><?php esc_html_e( 'Kostenlos und unverbindlich – Antwort meist in wenigen Stunden.', 'ruhrwerk24' ); ?></p>
			</div>
		<?php endif; ?>

		<div class="qf__progress" aria-hidden="true"><span></span></div>
		<p class="qf__status screen-reader-text" aria-live="polite"></p>

		<input type="hidden" name="action" value="rw24_lead">
		<input type="hidden" name="rw_token" value="<?php echo esc_attr( rw24_form_token() ); ?>">
		<input type="hidden" name="rw_source" value="<?php echo esc_url( $source ); ?>">
		<input type="hidden" name="rw_context" value="<?php echo esc_attr( $context ); ?>">

		<div class="qf__hp" aria-hidden="true">
			<label for="<?php echo esc_attr( $uid ); ?>-website"><?php esc_html_e( 'Website (bitte leer lassen)', 'ruhrwerk24' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $uid ); ?>-website" name="rw_website" tabindex="-1" autocomplete="off">
		</div>

		<div class="qf__steps">

			<fieldset class="qf__step" data-step="1">
				<?php $legend( __( 'Worum geht es?', 'ruhrwerk24' ) ); ?>
				<?php $radios( 'service', $choices['service'], $preset_service, true ); ?>
			</fieldset>

			<fieldset class="qf__step" data-step="2">
				<?php $legend( __( 'Wie dringend ist es?', 'ruhrwerk24' ) ); ?>
				<?php $radios( 'urgency', $choices['urgency'], rw24_form_old( 'urgency' ) ); ?>
				<p class="qf__alert">
					<?php echo rw24_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span>
						<?php esc_html_e( 'Bei akuten Schäden geht es am Telefon am schnellsten:', 'ruhrwerk24' ); ?>
						<a href="<?php echo esc_url( rw24_phone_href() ); ?>"><?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?></a>
					</span>
				</p>
			</fieldset>

			<fieldset class="qf__step" data-step="3">
				<?php $legend( __( 'Um was für ein Objekt handelt es sich?', 'ruhrwerk24' ) ); ?>
				<?php $radios( 'property', $choices['property'], rw24_form_old( 'property' ) ); ?>
			</fieldset>

			<fieldset class="qf__step" data-step="4">
				<?php $legend( __( 'Wo sollen wir hinkommen?', 'ruhrwerk24' ) ); ?>
				<div class="qf__row">
					<div class="qf__field qf__field--zip">
						<label for="<?php echo esc_attr( $uid ); ?>-zip"><?php esc_html_e( 'PLZ', 'ruhrwerk24' ); ?></label>
						<input type="text" id="<?php echo esc_attr( $uid ); ?>-zip" name="zip" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" autocomplete="postal-code" required value="<?php echo esc_attr( $preset_zip ); ?>" placeholder="<?php echo esc_attr( $city ? $city['zip'] : '45127' ); ?>">
					</div>
					<div class="qf__field">
						<label for="<?php echo esc_attr( $uid ); ?>-place"><?php esc_html_e( 'Ort', 'ruhrwerk24' ); ?> <span class="qf__opt"><?php esc_html_e( '(optional)', 'ruhrwerk24' ); ?></span></label>
						<input type="text" id="<?php echo esc_attr( $uid ); ?>-place" name="place" maxlength="80" autocomplete="address-level2" value="<?php echo esc_attr( $preset_city ); ?>">
					</div>
				</div>
			</fieldset>

			<fieldset class="qf__step" data-step="5">
				<?php $legend( __( 'Beschreiben Sie kurz, was los ist.', 'ruhrwerk24' ), __( 'Optional – hilft uns aber, das richtige Werkzeug mitzubringen.', 'ruhrwerk24' ) ); ?>
				<div class="qf__field">
					<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-message"><?php esc_html_e( 'Beschreibung', 'ruhrwerk24' ); ?></label>
					<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="message" rows="4" maxlength="2000" placeholder="<?php esc_attr_e( 'z. B. Toilette im Erdgeschoss läuft nicht ab, seit heute Morgen.', 'ruhrwerk24' ); ?>"><?php echo esc_textarea( rw24_form_old( 'message' ) ); ?></textarea>
				</div>
			</fieldset>

			<fieldset class="qf__step" data-step="6">
				<?php $legend( __( 'Wie heißen Sie?', 'ruhrwerk24' ) ); ?>
				<div class="qf__field">
					<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-name"><?php esc_html_e( 'Vor- und Nachname', 'ruhrwerk24' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-name" name="name" minlength="2" maxlength="80" autocomplete="name" required value="<?php echo esc_attr( rw24_form_old( 'name' ) ); ?>" placeholder="<?php esc_attr_e( 'Vor- und Nachname', 'ruhrwerk24' ); ?>">
				</div>
			</fieldset>

			<fieldset class="qf__step" data-step="7">
				<?php $legend( __( 'Unter welcher Nummer erreichen wir Sie?', 'ruhrwerk24' ), __( 'Optional, aber oft der schnellste Weg zum Termin.', 'ruhrwerk24' ) ); ?>
				<div class="qf__field">
					<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-phone"><?php esc_html_e( 'Telefon', 'ruhrwerk24' ); ?></label>
					<input type="tel" id="<?php echo esc_attr( $uid ); ?>-phone" name="phone" maxlength="40" autocomplete="tel" value="<?php echo esc_attr( rw24_form_old( 'phone' ) ); ?>" placeholder="<?php esc_attr_e( 'z. B. 0201 123456', 'ruhrwerk24' ); ?>">
				</div>
			</fieldset>

			<fieldset class="qf__step" data-step="8">
				<?php $legend( __( 'Wohin dürfen wir das Angebot schicken?', 'ruhrwerk24' ) ); ?>
				<div class="qf__field">
					<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'E-Mail-Adresse', 'ruhrwerk24' ); ?></label>
					<input type="email" id="<?php echo esc_attr( $uid ); ?>-email" name="email" maxlength="120" autocomplete="email" required value="<?php echo esc_attr( rw24_form_old( 'email' ) ); ?>" placeholder="<?php esc_attr_e( 'name@beispiel.de', 'ruhrwerk24' ); ?>">
				</div>
				<label class="qf__consent" for="<?php echo esc_attr( $uid ); ?>-consent">
					<input type="checkbox" id="<?php echo esc_attr( $uid ); ?>-consent" name="consent" value="1" required>
					<span>
						<?php
						$privacy = get_privacy_policy_url();
						printf(
							/* translators: %s: Link zur Datenschutzerklärung. */
							esc_html__( 'Ich bin einverstanden, dass meine Angaben zur Bearbeitung der Anfrage gespeichert werden. Mehr dazu in der %s.', 'ruhrwerk24' ),
							$privacy ? '<a href="' . esc_url( $privacy ) . '" target="_blank" rel="noopener">' . esc_html__( 'Datenschutzerklärung', 'ruhrwerk24' ) . '</a>' : esc_html__( 'Datenschutzerklärung', 'ruhrwerk24' )
						);
						?>
					</span>
				</label>
			</fieldset>

		</div>

		<div class="qf__nav">
			<button type="button" class="btn btn--quiet qf__back" hidden><?php esc_html_e( 'Zurück', 'ruhrwerk24' ); ?></button>
			<button type="button" class="btn btn--primary qf__next" hidden><?php esc_html_e( 'Weiter', 'ruhrwerk24' ); ?> <?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			<button type="submit" class="btn btn--primary qf__submit"><?php esc_html_e( 'Anfrage senden', 'ruhrwerk24' ); ?> <?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		</div>

		<p class="qf__trust">
			<?php echo rw24_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php esc_html_e( 'Ihre Daten bleiben bei uns. Keine Weitergabe, keine Werbung.', 'ruhrwerk24' ); ?>
		</p>
	</form>
	<?php
}

/**
 * IP-Adresse gehasht - wir speichern für die Sperre keine Klartext-IP.
 */
function rw24_client_fingerprint(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	return substr( hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ), 0, 20 );
}

/**
 * Formular verarbeiten.
 */
function rw24_handle_lead(): void {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Schutz über signierten Zeitstempel, siehe Dateikopf.
	$field = static fn( string $key ): string => isset( $_POST[ $key ] ) ? trim( (string) wp_unslash( $_POST[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- unten je Feld.

	// Honeypot gefüllt: Bot. Er bekommt die Erfolgsseite und erfährt nichts.
	if ( '' !== $field( 'rw_website' ) ) {
		wp_safe_redirect( rw24_form_page_url( array( 'anfrage' => 'ok' ) ) );
		exit;
	}

	$choices = rw24_form_choices();

	$data = array(
		'service'  => sanitize_key( $field( 'service' ) ),
		'urgency'  => sanitize_key( $field( 'urgency' ) ),
		'property' => sanitize_key( $field( 'property' ) ),
		'zip'      => preg_replace( '/\D/', '', $field( 'zip' ) ),
		'place'    => sanitize_text_field( $field( 'place' ) ),
		'message'  => sanitize_textarea_field( $field( 'message' ) ),
		'name'     => sanitize_text_field( $field( 'name' ) ),
		'phone'    => preg_replace( '/[^\d+\/\-\s()]/', '', $field( 'phone' ) ),
		'email'    => sanitize_email( $field( 'email' ) ),
		'source'   => esc_url_raw( $field( 'rw_source' ) ),
	);

	$errors = array();

	if ( ! rw24_form_token_valid( $field( 'rw_token' ) ) ) {
		$errors[] = 'token';
	}

	foreach ( array( 'service', 'urgency', 'property' ) as $key ) {
		if ( ! isset( $choices[ $key ][ $data[ $key ] ] ) ) {
			$errors[] = $key;
		}
	}

	if ( 5 !== strlen( $data['zip'] ) ) {
		$errors[] = 'zip';
	}

	if ( mb_strlen( $data['name'] ) < 2 ) {
		$errors[] = 'name';
	}

	if ( ! is_email( $data['email'] ) ) {
		$errors[] = 'email';
	}

	if ( '1' !== $field( 'consent' ) ) {
		$errors[] = 'consent';
	}

	// Höchstens fünf Anfragen pro Stunde von derselben Adresse.
	$limit_key = 'rw24_rate_' . rw24_client_fingerprint();
	$count     = (int) get_transient( $limit_key );

	if ( $count >= (int) apply_filters( 'rw24_lead_rate_limit', 5 ) ) {
		$errors[] = 'rate';
	}
	// phpcs:enable

	if ( $errors ) {
		// Eingaben für zehn Minuten merken, damit nichts neu getippt werden muss.
		$key = strtolower( wp_generate_password( 12, false ) );
		set_transient( 'rw24_form_' . $key, $data, 10 * MINUTE_IN_SECONDS );

		wp_safe_redirect( rw24_form_page_url( array( 'anfrage' => 'fehler', 'f' => $key ) ) . '#anfrage' );
		exit;
	}

	set_transient( $limit_key, $count + 1, HOUR_IN_SECONDS );

	$lead = rw24_create_lead( $data );

	if ( ! $lead ) {
		wp_safe_redirect( rw24_form_page_url( array( 'anfrage' => 'fehler' ) ) . '#anfrage' );
		exit;
	}

	rw24_send_lead_mails( $lead['id'] );

	wp_safe_redirect( rw24_form_page_url( array( 'anfrage' => 'ok', 'nr' => $lead['ref'] ) ) );
	exit;
}
add_action( 'admin_post_nopriv_rw24_lead', 'rw24_handle_lead' );
add_action( 'admin_post_rw24_lead', 'rw24_handle_lead' );
