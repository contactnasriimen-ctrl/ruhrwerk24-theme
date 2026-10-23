<?php
/**
 * Hero einer Leistung mit Foto und Anfrageformular.
 *
 * Wird von der Leistungsseite und den Stadtseiten genutzt. Muss innerhalb
 * des Loops stehen.
 *
 * Argumente:
 *   title  Überschrift (Standard: "<Leistung> in NRW")
 *   lead   Einleitung (Standard: Kurzversprechen)
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_id        = get_the_ID();
$rw_hero      = rw24_service_image( $rw_id, 'hero' );
$rw_emergency = '1' === get_post_meta( $rw_id, '_rw_emergency', true );
$rw_groups    = get_the_terms( $rw_id, 'rw_service_group' );

/* translators: %s: Name der Leistung. */
$rw_title = $args['title'] ?? sprintf( __( '%s in NRW', 'ruhrwerk24' ), get_the_title() );
$rw_lead  = $args['lead'] ?? (string) get_post_meta( $rw_id, '_rw_short_claim', true );
?>
<section class="service-hero on-dark">
	<div class="service-hero__media">
		<?php
		rw24_media(
			$rw_hero,
			'hero',
			get_the_title(),
			true,
			/* translators: %s: Name der Leistung. */
			sprintf( __( 'Hero-Foto %s · 16:9 · 1920×1080', 'ruhrwerk24' ), get_the_title() )
		);
		?>
	</div>
	<div class="bg-anim" aria-hidden="true"><span></span><span></span><span></span></div>

	<?php if ( ! $rw_hero ) : ?>
		<?php // Ohne Foto übernimmt das 3D-Rohrnetz die Bühne. ?>
		<div class="scene3d" data-scene3d="service" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="container service-hero__inner">
		<?php rw24_breadcrumbs(); ?>

		<div class="hero__layout">
			<div class="service-hero__content">
				<div class="service-hero__tags">
					<?php if ( $rw_groups && ! is_wp_error( $rw_groups ) ) : ?>
						<a class="eyebrow" href="<?php echo esc_url( get_term_link( $rw_groups[0] ) ); ?>">
							<?php echo esc_html( $rw_groups[0]->name ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $rw_emergency ) : ?>
						<span class="emergency__tag"><span class="dot" aria-hidden="true"></span> <?php esc_html_e( 'Notdienst 24/7', 'ruhrwerk24' ); ?></span>
					<?php endif; ?>
				</div>

				<h1 class="split-words"><?php echo esc_html( $rw_title ); ?></h1>

				<?php if ( $rw_lead ) : ?>
					<p class="lead"><?php echo esc_html( $rw_lead ); ?></p>
				<?php endif; ?>

				<div class="hero__actions">
					<a class="btn btn--primary btn--wide hero__jump" href="#anfrage">
						<?php esc_html_e( 'Anfrage in 60 Sekunden', 'ruhrwerk24' ); ?>
					</a>
					<a class="btn btn--ghost btn--wide" href="<?php echo esc_url( rw24_phone_href() ); ?>">
						<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
					</a>
				</div>
			</div>

			<div class="hero__form" id="anfrage">
				<?php rw24_render_form( 'hero' ); ?>
			</div>
		</div>
	</div>
</section>
