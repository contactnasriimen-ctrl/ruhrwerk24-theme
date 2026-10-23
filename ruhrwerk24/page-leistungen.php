<?php
/**
 * Template Name: Leistungsübersicht
 *
 * Für eine eigene Seite mit allen Leistungen und zusätzlichem Text aus
 * dem Editor. Das Raster teilt sich diese Vorlage mit /leistungen/.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="inhalt">

		<?php
		get_template_part(
			'template-parts/pagehead',
			null,
			array(
				'title' => get_the_title(),
				'lead'  => has_excerpt() ? get_the_excerpt() : __( 'Kanaltechnik, Haustechnik und Ausbau – ein Ansprechpartner für ganz Nordrhein-Westfalen.', 'ruhrwerk24' ),
			)
		);
		?>

		<?php get_template_part( 'template-parts/groups-nav' ); ?>

		<?php get_template_part( 'template-parts/service-groups' ); ?>

		<?php if ( get_the_content() ) : ?>
			<section class="section"><div class="container prose reveal"><?php the_content(); ?></div></section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/cta' ); ?>

	</main>
	<?php
endwhile;

get_footer();
