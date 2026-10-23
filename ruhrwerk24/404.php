<?php
/**
 * Fehlerseite.
 *
 * Statt einer Sackgasse: die beliebtesten Wege direkt anbieten.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="inhalt">

	<section class="pagehead pagehead--error on-dark">
		<div class="bg-anim" aria-hidden="true"><span></span><span></span><span></span></div>
		<div class="bg-grid" aria-hidden="true"></div>
		<div class="container pagehead__inner">
			<span class="eyebrow"><?php esc_html_e( 'Fehler 404', 'ruhrwerk24' ); ?></span>
			<h1><?php esc_html_e( 'Diese Seite gibt es nicht', 'ruhrwerk24' ); ?></h1>
			<p class="lead"><?php esc_html_e( 'Der Link ist vermutlich veraltet. Über die Leistungsübersicht finden Sie, was Sie suchen – oder Sie rufen uns einfach an.', 'ruhrwerk24' ); ?></p>
			<div class="hero__actions">
				<a class="btn btn--primary btn--wide" href="<?php echo esc_url( get_post_type_archive_link( 'rw_service' ) ); ?>"><?php esc_html_e( 'Zu den Leistungen', 'ruhrwerk24' ); ?></a>
				<a class="btn btn--ghost btn--wide" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Zur Startseite', 'ruhrwerk24' ); ?></a>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/service-groups' ); ?>

</main>
<?php
get_footer();
