<?php
/**
 * Template Name: Anfrage
 *
 * Vollbild-Anfrageseite mit Danke-Ansicht.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- reine Anzeige-Parameter.
$rw_state = isset( $_GET['anfrage'] ) ? sanitize_key( wp_unslash( $_GET['anfrage'] ) ) : '';
$rw_ref   = isset( $_GET['nr'] ) ? sanitize_text_field( wp_unslash( $_GET['nr'] ) ) : '';
// phpcs:enable

// Nur echte Anfragenummern anzeigen - die Seite soll keinen beliebigen
// Text aus der URL in großer Schrift wiedergeben.
if ( ! preg_match( '/^RW-\d{4}-\d{4}$/', $rw_ref ) ) {
	$rw_ref = '';
}

while ( have_posts() ) :
	the_post();
	?>
	<main id="inhalt" class="section section--grey anfrage">
		<div class="container">

			<?php if ( 'ok' === $rw_state ) : ?>

				<div class="thanks reveal is-in">
					<div class="thanks__mark" aria-hidden="true"><?php echo rw24_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<h1><?php esc_html_e( 'Danke, Ihre Anfrage ist da.', 'ruhrwerk24' ); ?></h1>

					<?php if ( $rw_ref ) : ?>
						<div class="refbox">
							<span class="refbox__label"><?php esc_html_e( 'Ihre Anfragenummer', 'ruhrwerk24' ); ?></span>
							<strong class="refbox__nr"><?php echo esc_html( $rw_ref ); ?></strong>
						</div>
						<p class="lead"><?php esc_html_e( 'Bitte notieren Sie sich diese Nummer. Sie steht auch in der Bestätigungsmail, die wir Ihnen gerade geschickt haben. Bei Rückfragen finden wir Ihre Anfrage darüber sofort wieder.', 'ruhrwerk24' ); ?></p>
					<?php else : ?>
						<p class="lead"><?php esc_html_e( 'Wir melden uns in der Regel innerhalb weniger Stunden. Bei dringenden Fällen rufen Sie uns bitte direkt an.', 'ruhrwerk24' ); ?></p>
					<?php endif; ?>

					<ol class="thanks__next">
						<li><?php esc_html_e( 'Wir prüfen Ihre Angaben.', 'ruhrwerk24' ); ?></li>
						<li><?php esc_html_e( 'Ein Fachmann ruft Sie zurück oder schreibt Ihnen.', 'ruhrwerk24' ); ?></li>
						<li><?php esc_html_e( 'Sie erhalten einen Festpreis – bevor wir anfangen.', 'ruhrwerk24' ); ?></li>
					</ol>

					<div class="hero__actions hero__actions--center">
						<a class="btn btn--primary btn--wide" href="<?php echo esc_url( rw24_phone_href() ); ?>">
							<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
						</a>
						<a class="btn btn--outline btn--wide" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Zur Startseite', 'ruhrwerk24' ); ?></a>
					</div>
				</div>

			<?php else : ?>

				<div class="head head--center reveal">
					<h1><?php the_title(); ?></h1>
					<p><?php esc_html_e( 'Acht kurze Fragen. Danach wissen wir genug, um Ihnen eine belastbare Einschätzung zu geben.', 'ruhrwerk24' ); ?></p>
				</div>

				<?php if ( 'fehler' === $rw_state ) : ?>
					<p class="notice notice--error" role="alert">
						<?php echo rw24_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Da ist etwas schiefgelaufen. Bitte prüfen Sie PLZ, Name und E-Mail-Adresse und senden Sie erneut. Ihre Angaben haben wir aufbewahrt.', 'ruhrwerk24' ); ?></span>
					</p>
				<?php endif; ?>

				<div class="qf-wrap reveal" id="anfrage">
					<?php rw24_render_form( 'page' ); ?>
				</div>

				<ul class="anfrage__trust">
					<li><?php echo rw24_icon( 'euro' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Kostenlos und unverbindlich', 'ruhrwerk24' ); ?></li>
					<li><?php echo rw24_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Antwort meist in wenigen Stunden', 'ruhrwerk24' ); ?></li>
					<li><?php echo rw24_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Keine Weitergabe Ihrer Daten', 'ruhrwerk24' ); ?></li>
				</ul>

				<?php if ( get_the_content() ) : ?>
					<div class="prose anfrage__content"><?php the_content(); ?></div>
				<?php endif; ?>

			<?php endif; ?>

		</div>
	</main>
	<?php
endwhile;

get_footer();
