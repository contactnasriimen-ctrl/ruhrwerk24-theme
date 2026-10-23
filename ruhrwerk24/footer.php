<?php
/**
 * Fußbereich: 3D-Karte NRW mit letztem Aufruf, Kontaktdaten, Leistungsgruppen,
 * Einsatzgebiete, Schriftzug, "Nach oben" mit Fortschrittsring und Sticky-Leiste.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_groups   = rw24_grouped_services();
$rw_top      = array_slice( rw24_cities(), 0, 8, true );
$rw_areas    = rw24_areas_url();
$rw_whatsapp = rw24_whatsapp_href();

// Punkte für die 3D-Karte: alle Städte mit Koordinaten, der Firmensitz als Zentrale.
$rw_hub    = sanitize_title( rw24_contact( 'rw_city' ) );
$rw_points = array();
foreach ( rw24_cities() as $rw_slug => $rw_city ) {
	if ( empty( $rw_city['geo'] ) ) {
		continue;
	}
	$rw_points[] = array(
		'n'   => $rw_city['name'],
		'lat' => $rw_city['geo'][0],
		'lon' => $rw_city['geo'][1],
		'hub' => sanitize_title( $rw_city['name'] ) === $rw_hub,
	);
}
?>
<footer class="footer on-dark">

	<?php // Oberer Teil: 3D-Karte des Einsatzgebiets und letzter Aufruf. ?>
	<div class="footer__top">
		<?php if ( $rw_points ) : ?>
			<div class="footer__map" aria-hidden="true">
				<div class="scene3d scene3d--stage" data-scene3d="nrw" data-scene3d-desktop data-points="<?php echo esc_attr( wp_json_encode( $rw_points ) ); ?>"></div>
			</div>
		<?php endif; ?>

		<div class="container footer__cta reveal">
			<span class="footer__live"><span class="dot" aria-hidden="true"></span> <?php esc_html_e( 'Notdienst jetzt erreichbar', 'ruhrwerk24' ); ?></span>
			<p class="footer__claim">
				<?php esc_html_e( 'Ein Anruf.', 'ruhrwerk24' ); ?>
				<span><?php esc_html_e( 'Ein Ansprechpartner.', 'ruhrwerk24' ); ?></span>
				<em>
					<?php
					/* translators: %d: Anzahl der Städte. */
					echo esc_html( sprintf( __( '%d Städte in NRW.', 'ruhrwerk24' ), count( rw24_cities() ) ) );
					?>
				</em>
			</p>
			<div class="hero__actions">
				<a class="btn btn--primary btn--wide" href="<?php echo esc_url( rw24_phone_href() ); ?>">
					<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
				</a>
				<a class="btn btn--ghost btn--wide" href="<?php echo esc_url( rw24_form_page_url() ); ?>">
					<?php esc_html_e( 'Anfrage in 60 Sekunden', 'ruhrwerk24' ); ?>
					<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="footer__grid">

			<div class="footer__brand reveal">
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

			<?php foreach ( $rw_groups as $rw_i => $rw_group ) : ?>
				<div class="reveal" data-delay="<?php echo esc_attr( (string) ( $rw_i % 4 + 1 ) ); ?>">
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
				<div class="reveal" data-delay="4">
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

		<?php // Riesiger Schriftzug mit wanderndem Glanzlicht (folgt der Maus). ?>
		<div class="footer__mark" aria-hidden="true">Ruhrwerk<span>24</span></div>

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
			<a class="totop" href="#header">
				<svg class="totop__ring" viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="21" pathLength="100"></circle></svg>
				<?php echo rw24_icon( 'arrow', 'totop__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Nach oben', 'ruhrwerk24' ); ?></span>
			</a>
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
