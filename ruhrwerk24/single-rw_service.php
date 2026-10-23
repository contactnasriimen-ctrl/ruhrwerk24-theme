<?php
/**
 * Einzelne Leistung.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$rw_id        = get_the_ID();
	$rw_emergency = '1' === get_post_meta( $rw_id, '_rw_emergency', true );

	// Weitere Leistungen derselben Gruppe.
	$rw_related = array();
	foreach ( rw24_grouped_services() as $rw_group ) {
		if ( has_term( $rw_group['term']->term_id, 'rw_service_group', $rw_id ) ) {
			$rw_related = array_filter( $rw_group['posts'], static fn( WP_Post $p ): bool => $p->ID !== $rw_id );
			break;
		}
	}
	?>

	<main id="inhalt">

		<?php get_template_part( 'template-parts/service-hero' ); ?>

		<?php get_template_part( 'template-parts/service-body' ); ?>

		<?php get_template_part( 'template-parts/faq', null, array( 'items' => rw24_service_faq( $rw_id ) ) ); ?>

		<?php if ( $rw_emergency ) : ?>
			<?php get_template_part( 'template-parts/emergency' ); ?>
		<?php endif; ?>

		<?php if ( $rw_related ) : ?>
			<section class="section">
				<div class="container">
					<div class="head reveal">
						<h2><?php esc_html_e( 'Das könnte auch passen', 'ruhrwerk24' ); ?></h2>
					</div>
					<div class="grid">
						<?php
						foreach ( array_values( $rw_related ) as $rw_i => $rw_post ) {
							get_template_part( 'template-parts/service-card', null, array( 'service' => $rw_post, 'delay' => $rw_i % 3 + 1 ) );
						}
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		get_template_part(
			'template-parts/areas-teaser',
			null,
			array(
				'service' => get_post(),
				/* translators: %s: Leistung. */
				'title'   => sprintf( __( '%s in Ihrer Stadt', 'ruhrwerk24' ), get_the_title() ),
				'lead'    => __( 'Wir sind in ganz NRW unterwegs. Hier finden Sie die Seite für Ihren Ort.', 'ruhrwerk24' ),
			)
		);
		?>

		<?php get_template_part( 'template-parts/cta' ); ?>

	</main>

	<?php
endwhile;

get_footer();
