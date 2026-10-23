<?php
/**
 * Abschluss-Band: Anfrage oder Anruf.
 *
 * Argumente:
 *   title  abweichende Überschrift (z. B. mit Stadtname)
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_title = $args['title'] ?? __( 'Sagen Sie uns, worum es geht.', 'ruhrwerk24' );
?>
<section class="cta on-dark">
	<div class="bg-grid" aria-hidden="true"></div>
	<div class="container cta__inner reveal">
		<div>
			<h2><?php echo esc_html( $rw_title ); ?></h2>
			<p><?php esc_html_e( 'Acht kurze Fragen, eine Minute Zeit. Sie bekommen eine ehrliche Einschätzung und einen Festpreis – bevor jemand anrückt.', 'ruhrwerk24' ); ?></p>
		</div>
		<div class="cta__actions">
			<a class="btn btn--primary btn--wide" href="<?php echo esc_url( rw24_form_page_url() ); ?>">
				<?php esc_html_e( 'Anfrage in 60 Sekunden', 'ruhrwerk24' ); ?>
				<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<a class="btn btn--ghost btn--wide" href="<?php echo esc_url( rw24_phone_href() ); ?>">
				<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
			</a>
		</div>
	</div>
</section>
