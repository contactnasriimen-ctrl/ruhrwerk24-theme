<?php
/**
 * Fallback-Template: Beitragsliste, Archive, Suche.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_search() ) {
	/* translators: %s: Suchbegriff. */
	$rw_title = sprintf( __( 'Suche nach „%s“', 'ruhrwerk24' ), get_search_query() );
	$rw_lead  = '';
} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
	$rw_title = get_the_title( (int) get_option( 'page_for_posts' ) );
	$rw_lead  = __( 'Tipps rund um Haus, Rohr und Technik – von unseren Fachleuten.', 'ruhrwerk24' );
} elseif ( is_archive() ) {
	$rw_title = wp_strip_all_tags( get_the_archive_title() );
	$rw_lead  = wp_strip_all_tags( get_the_archive_description() );
} else {
	$rw_title = __( 'Ratgeber', 'ruhrwerk24' );
	$rw_lead  = '';
}
?>
<main id="inhalt">

	<?php get_template_part( 'template-parts/pagehead', null, array( 'title' => $rw_title, 'lead' => $rw_lead ) ); ?>

	<section class="section">
		<div class="container">
			<?php if ( have_posts() ) : ?>
				<div class="grid">
					<?php
					$rw_i = 0;
					while ( have_posts() ) :
						the_post();
						?>
						<article <?php post_class( 'card reveal' ); ?> data-delay="<?php echo esc_attr( (string) ( $rw_i++ % 3 + 1 ) ); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php rw24_media( (int) get_post_thumbnail_id(), 'card', get_the_title() ); ?>
							<?php endif; ?>
							<div class="card__body">
								<p class="card__meta"><?php echo esc_html( get_the_date() ); ?></p>
								<h2 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
								<p class="card__text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
								<span class="card__more" aria-hidden="true"><?php esc_html_e( 'Weiterlesen', 'ruhrwerk24' ); ?> <?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</div>
						</article>
					<?php endwhile; ?>
				</div>

				<?php
				the_posts_pagination(
					array(
						'prev_text' => __( 'Zurück', 'ruhrwerk24' ),
						'next_text' => __( 'Weiter', 'ruhrwerk24' ),
					)
				);
				?>
			<?php else : ?>
				<div class="prose">
					<h2><?php esc_html_e( 'Nichts gefunden', 'ruhrwerk24' ); ?></h2>
					<p><?php esc_html_e( 'Zu dieser Adresse gibt es keinen Inhalt. Versuchen Sie es mit einem anderen Suchbegriff:', 'ruhrwerk24' ); ?></p>
					<?php get_search_form(); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cta' ); ?>

</main>
<?php
get_footer();
