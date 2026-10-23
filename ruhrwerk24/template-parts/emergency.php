<?php
/**
 * Notdienst-Band.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="emergency on-dark">
	<div class="container emergency__inner reveal">
		<div class="emergency__stage" aria-hidden="true">
			<?php // 3D: undichte Muffe mit Tropfen und Blaulicht. Ohne WebGL bleibt das Symbol. ?>
			<div class="scene3d scene3d--stage" data-scene3d="drops" data-scene3d-desktop></div>
			<div class="emergency__icon"><?php echo rw24_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		</div>
		<div class="emergency__text">
			<span class="emergency__tag"><span class="dot" aria-hidden="true"></span> <?php esc_html_e( 'Notdienst 24/7', 'ruhrwerk24' ); ?></span>
			<h2><?php echo esc_html( rw24_contact( 'rw_em_title' ) ); ?></h2>
			<p><?php echo esc_html( rw24_contact( 'rw_em_text' ) ); ?></p>
		</div>
		<div class="emergency__actions">
			<a class="btn btn--emergency btn--wide pulse" href="<?php echo esc_url( rw24_phone_href() ); ?>">
				<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
			</a>
			<a class="emergency__more" href="<?php echo esc_url( home_url( '/notdienst/' ) ); ?>"><?php esc_html_e( 'Wobei wir sofort helfen', 'ruhrwerk24' ); ?></a>
		</div>
	</div>
</section>
