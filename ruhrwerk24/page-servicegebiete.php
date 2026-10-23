<?php
/**
 * Template Name: Servicegebiete
 *
 * Übersicht aller Städte, gruppiert nach Region. Von hier führt ein Link
 * auf jede Stadtseite – wichtig, damit Google die Landingpages überhaupt
 * findet und nicht nur die Sitemap sie kennt.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Nach Region bündeln, in der Reihenfolge des ersten Auftretens.
$rw_regions = array();

foreach ( rw24_cities() as $rw_slug => $rw_city ) {
	$rw_regions[ $rw_city['region'] ][ $rw_slug ] = $rw_city;
}

$rw_services = array_slice( rw24_all_services(), 0, 4 );

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
				'lead'  => sprintf(
					/* translators: %d: Anzahl der Städte. */
					__( 'Wir arbeiten in %d Städten in Nordrhein-Westfalen. Wählen Sie Ihren Ort, um zu sehen, was wir dort anbieten.', 'ruhrwerk24' ),
					count( rw24_cities() )
				),
			)
		);
		?>

		<nav class="subnav" aria-label="<?php esc_attr_e( 'Regionen', 'ruhrwerk24' ); ?>">
			<div class="container">
				<ul>
					<?php foreach ( $rw_regions as $rw_region => $rw_cities ) : ?>
						<li><a href="#<?php echo esc_attr( sanitize_title( $rw_region ) ); ?>"><?php echo esc_html( $rw_region ); ?> <span><?php echo esc_html( (string) count( $rw_cities ) ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</nav>

		<?php $rw_i = 0; ?>
		<?php foreach ( $rw_regions as $rw_region => $rw_cities ) : ?>
			<section class="section<?php echo 1 === $rw_i++ % 2 ? ' section--grey' : ''; ?>" id="<?php echo esc_attr( sanitize_title( $rw_region ) ); ?>">
				<div class="container">
					<div class="head reveal">
						<h2><?php echo esc_html( $rw_region ); ?></h2>
					</div>

					<div class="citygrid">
						<?php foreach ( $rw_cities as $rw_slug => $rw_city ) : ?>
							<article class="citycard reveal">
								<h3><a href="<?php echo esc_url( rw24_city_hub_url( $rw_slug ) ); ?>"><?php echo esc_html( $rw_city['name'] ); ?></a></h3>
								<p class="citycard__meta">
									<?php echo esc_html( $rw_city['zip'] . ' · ' . rw24_city_district( $rw_city ) ); ?>
								</p>
								<p class="citycard__parts">
									<?php echo esc_html( implode( ', ', $rw_city['parts'] ) ); ?>
								</p>
								<?php if ( $rw_services ) : ?>
									<ul class="citycard__links">
										<?php foreach ( $rw_services as $rw_service ) : ?>
											<li>
												<a href="<?php echo esc_url( rw24_city_url( $rw_service, $rw_slug ) ); ?>">
													<?php echo esc_html( get_the_title( $rw_service ) ); ?>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
								<a class="citycard__all link-arrow" href="<?php echo esc_url( rw24_city_hub_url( $rw_slug ) ); ?>">
									<?php
									printf(
										/* translators: %s: Stadt. */
										esc_html__( 'Alle Leistungen in %s', 'ruhrwerk24' ),
										esc_html( $rw_city['name'] )
									);
									?>
									<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endforeach; ?>

		<?php if ( get_the_content() ) : ?>
			<section class="section"><div class="container prose reveal"><?php the_content(); ?></div></section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/cta' ); ?>

	</main>
	<?php
endwhile;

get_footer();
