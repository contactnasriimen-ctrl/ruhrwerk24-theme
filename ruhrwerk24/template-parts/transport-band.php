<?php
/**
 * Band "Umzug & Transport" mit 3D-Transporter.
 *
 * Erscheint nur, wenn die Gruppe Leistungen hat. Der Transporter
 * (hero3d.js, Variante "truck") fährt durch eine nächtliche Stadt,
 * Umzugskartons schweben hinterher.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_group = null;
foreach ( rw24_grouped_services() as $rw_g ) {
	if ( rw24_transport_group() === $rw_g['term']->slug ) {
		$rw_group = $rw_g;
		break;
	}
}

if ( ! $rw_group ) {
	return;
}

$rw_lead = $rw_group['posts'][0];
?>
<section class="transport on-dark" aria-labelledby="transport-title">
	<div class="bg-grid" aria-hidden="true"></div>
	<div class="container transport__inner">
		<div class="transport__text reveal">
			<span class="emergency__tag transport__tag"><?php echo rw24_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $rw_group['term']->name ); ?></span>
			<h2 id="transport-title"><?php esc_html_e( 'Umziehen, ohne selbst zu schleppen.', 'ruhrwerk24' ); ?></h2>
			<p><?php echo esc_html( $rw_group['term']->description ); ?></p>

			<ul class="transport__services">
				<?php foreach ( $rw_group['posts'] as $rw_service ) : ?>
					<li>
						<a href="<?php echo esc_url( get_permalink( $rw_service ) ); ?>">
							<?php echo rw24_icon( rw24_service_icon( $rw_service->ID ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( get_the_title( $rw_service ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="hero__actions">
				<a class="btn btn--primary btn--wide" href="<?php echo esc_url( rw24_form_page_url( array( 'leistung' => $rw_lead->post_name ) ) ); ?>">
					<?php
					/* translators: %s: Leistung, z. B. Umzug. */
					echo esc_html( sprintf( __( '%s anfragen', 'ruhrwerk24' ), get_the_title( $rw_lead ) ) );
					?>
					<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<a class="btn btn--ghost btn--wide" href="<?php echo esc_url( rw24_phone_href() ); ?>">
					<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
				</a>
			</div>
		</div>

		<div class="transport__stage" aria-hidden="true">
			<div class="scene3d scene3d--stage" data-scene3d="truck" data-scene3d-desktop></div>
			<div class="transport__fallback"><?php echo rw24_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		</div>
	</div>
</section>
