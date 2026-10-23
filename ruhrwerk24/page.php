<?php
/**
 * Standard-Template für Seiten.
 *
 * Gleicher Aufbau wie die übrigen Seiten des Themes: dunkler Seitenkopf
 * mit animiertem Hintergrund, darunter der Inhalt in lesbarer Spaltenbreite.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$rw_thumb = (int) get_post_thumbnail_id();
	?>

	<main id="inhalt">

		<?php
		get_template_part(
			'template-parts/pagehead',
			null,
			array(
				'title' => get_the_title(),
				'lead'  => has_excerpt() ? get_the_excerpt() : '',
			)
		);
		?>

		<section class="section">
			<div class="container container--narrow">

				<?php if ( $rw_thumb ) : ?>
					<div class="reveal page-media">
						<?php rw24_media( $rw_thumb, 'reference', get_the_title(), true ); ?>
					</div>
				<?php endif; ?>

				<div class="prose reveal">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<p class="pagelinks">' . esc_html__( 'Seiten:', 'ruhrwerk24' ) . ' ',
							'after'  => '</p>',
						)
					);
					?>
				</div>

			</div>
		</section>

		<?php
		// Auf den Rechtstexten wäre ein Verkaufsaufruf unpassend.
		if ( ! is_page( array( 'impressum', 'datenschutz', 'agb' ) ) && (int) get_option( 'wp_page_for_privacy_policy' ) !== get_the_ID() ) {
			get_template_part( 'template-parts/cta' );
		}
		?>

	</main>

	<?php
endwhile;

get_footer();
