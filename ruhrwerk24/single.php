<?php
/**
 * Einzelner Beitrag (Ratgeber).
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$rw_meta = sprintf(
		/* translators: 1: Datum, 2: Lesedauer in Minuten. */
		__( '%1$s · etwa %2$d Min. Lesezeit', 'ruhrwerk24' ),
		get_the_date(),
		rw24_reading_time( get_post_field( 'post_content' ) )
	);
	?>

	<main id="inhalt">

		<?php
		get_template_part(
			'template-parts/pagehead',
			null,
			array(
				'title' => get_the_title(),
				'meta'  => $rw_meta,
				'lead'  => has_excerpt() ? get_the_excerpt() : '',
			)
		);
		?>

		<article <?php post_class( 'section' ); ?>>
			<div class="container container--narrow">

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="reveal page-media">
						<?php rw24_media( (int) get_post_thumbnail_id(), 'reference', get_the_title(), true ); ?>
					</div>
				<?php endif; ?>

				<div class="prose reveal"><?php the_content(); ?></div>

				<?php
				the_post_navigation(
					array(
						'prev_text' => '<span class="postnav__label">' . esc_html__( 'Vorheriger Beitrag', 'ruhrwerk24' ) . '</span> %title',
						'next_text' => '<span class="postnav__label">' . esc_html__( 'Nächster Beitrag', 'ruhrwerk24' ) . '</span> %title',
						'class'     => 'postnav',
					)
				);
				?>
			</div>
		</article>

		<?php get_template_part( 'template-parts/cta' ); ?>

	</main>

	<?php
endwhile;

get_footer();
