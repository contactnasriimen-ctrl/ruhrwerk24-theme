<?php
/**
 * Fußbereich mit Kontaktdaten, Einsatzgebieten, Leistungsgruppen und Sticky-Leiste.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_groups   = rw24_grouped_services();
$rw_top      = array_slice( rw24_cities(), 0, 8, true );
$rw_areas    = rw24_areas_url();
$rw_whatsapp = rw24_whatsapp_href();
?>
<footer class="footer">
	<div class="container">
		<div class="footer__grid">

			<div class="footer__brand">
				<a class="logo logo--light" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="logo__mark" aria-hidden="true"><?php echo rw24_icon( 'tool' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span>Ruhrwerk<b>24</b></span>
				</a>
				<p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>

				<address class="footer__contact">
					<strong><?php echo esc_html( rw24_contact( 'rw_owner' ) ); ?></strong><br>
					<?php echo esc_html( rw24_contact( 'rw_street' ) ); ?><br>
					<?php echo esc_html( rw24_contact( 'rw_zip' ) . ' ' . rw24_contact( 'rw_city' ) ); ?>
				</address>

				<ul class="footer__links">
					<li><a href="<?php echo esc_url( rw24_phone_href() ); ?>"><?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?></a></li>
					<li><a href="mailto:<?php echo esc_attr( rw24_contact( 'rw_email' ) ); ?>"><?php echo rw24_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rw24_contact( 'rw_email' ) ); ?></a></li>
					<?php if ( $rw_whatsapp ) : ?>
						<li><a href="<?php echo esc_url( $rw_whatsapp ); ?>" rel="noopener" target="_blank"><?php echo rw24_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'WhatsApp', 'ruhrwerk24' ); ?></a></li>
					<?php endif; ?>
					<li><?php echo rw24_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rw24_contact( 'rw_hours' ) ); ?></li>
				</ul>
			</div>

			<?php foreach ( $rw_groups as $rw_group ) : ?>
				<div>
					<h2 class="footer__title">
						<a href="<?php echo esc_url( get_term_link( $rw_group['term'] ) ); ?>"><?php echo esc_html( $rw_group['term']->name ); ?></a>
					</h2>
					<ul>
						<?php foreach ( $rw_group['posts'] as $rw_service ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $rw_service ) ); ?>"><?php echo esc_html( get_the_title( $rw_service ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>

			<?php
			// Einsatzgebiete: die meistgefragten Städte direkt verlinken.
			// Das hilft Besuchern und gibt den Stadtseiten interne Links.
			if ( $rw_top && $rw_areas ) :
				?>
				<div>
					<h2 class="footer__title"><?php esc_html_e( 'Einsatzgebiete', 'ruhrwerk24' ); ?></h2>
					<ul>
						<?php foreach ( $rw_top as $rw_slug => $rw_city ) : ?>
							<li><a href="<?php echo esc_url( rw24_city_hub_url( $rw_slug ) ); ?>"><?php echo esc_html( $rw_city['name'] ); ?></a></li>
						<?php endforeach; ?>
						<li><a class="footer__all" href="<?php echo esc_url( $rw_areas ); ?>"><?php esc_html_e( 'Alle Städte', 'ruhrwerk24' ); ?></a></li>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<div>
					<h2 class="footer__title"><?php esc_html_e( 'Unternehmen', 'ruhrwerk24' ); ?></h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'depth'          => 1,
						)
					);
					?>
				</div>
			<?php endif; ?>

		</div>

		<div class="footer__bar">
			<span>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
			<?php
			wp_nav_menu(
				array(
					'theme_location'       => 'legal',
					'container'            => 'nav',
					'container_aria_label' => __( 'Rechtliches', 'ruhrwerk24' ),
					'menu_class'           => 'footer__legal',
					'depth'                => 1,
					'fallback_cb'          => false,
				)
			);
			?>
			<span class="footer__credit"><?php echo wp_kses_post( rw24_credit() ); ?></span>
		</div>
	</div>
</footer>

<nav class="callbar" id="callbar" aria-label="<?php esc_attr_e( 'Schnellkontakt', 'ruhrwerk24' ); ?>">
	<a class="callbar__call" href="<?php echo esc_url( rw24_phone_href() ); ?>">
		<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php esc_html_e( 'Anrufen', 'ruhrwerk24' ); ?>
	</a>
	<a class="callbar__form" href="<?php echo esc_url( rw24_form_page_url() ); ?>">
		<?php echo rw24_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php esc_html_e( 'Anfrage', 'ruhrwerk24' ); ?>
	</a>
	<a class="callbar__sos" href="<?php echo esc_url( home_url( '/notdienst/' ) ); ?>">
		<?php echo rw24_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php esc_html_e( 'Notdienst', 'ruhrwerk24' ); ?>
	</a>
</nav>

<?php wp_footer(); ?>
</body>
</html>
