<?php
/**
 * Leistungsgruppe, z. B. /leistungsbereich/kanaltechnik/.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="inhalt">

	<?php
	get_template_part(
		'template-parts/pagehead',
		null,
		array(
			'title' => single_term_title( '', false ),
			'lead'  => wp_strip_all_tags( term_description() ),
		)
	);
	?>

	<section class="section">
		<div class="container">
			<?php if ( have_posts() ) : ?>
				<div class="grid">
					<?php
					$rw_i = 0;
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/service-card', null, array( 'service' => get_post(), 'delay' => $rw_i++ % 3 + 1 ) );
					}
					?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'In dieser Gruppe sind noch keine Leistungen eingetragen.', 'ruhrwerk24' ); ?></p>
			<?php endif; ?>

			<p class="areas__all">
				<a class="link-arrow" href="<?php echo esc_url( get_post_type_archive_link( 'rw_service' ) ); ?>">
					<?php esc_html_e( 'Alle Leistungen ansehen', 'ruhrwerk24' ); ?>
					<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</p>
		</div>
	</section>

	<?php
	if ( rw24_transport_group() === get_queried_object()->slug ) {
		get_template_part( 'template-parts/transport-band' );
	}
	?>

	<?php get_template_part( 'template-parts/process' ); ?>

	<?php get_template_part( 'template-parts/hashtags' ); ?>

	<?php get_template_part( 'template-parts/cta' ); ?>

</main>
<?php
get_footer();
