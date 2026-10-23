<?php
/**
 * Stadtübersicht: alle Leistungen in einer Stadt, z. B. /servicegebiete/essen/.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_city      = rw24_current_city();
$rw_city_slug = rw24_current_city_slug();

get_header();
?>
<main id="inhalt">

	<?php
	get_template_part(
		'template-parts/pagehead',
		null,
		array(
			/* translators: %s: Stadt. */
			'title'   => sprintf( __( 'Ihr Handwerker in %s', 'ruhrwerk24' ), $rw_city['name'] ),
			'eyebrow' => $rw_city['zip'] . ' · ' . rw24_city_district( $rw_city ),
			'lead'    => sprintf(
				/* translators: 1: Stadt, 2: Stadtteile. */
				__( 'Kanaltechnik, Haustechnik und Ausbau aus einer Hand – in ganz %1$s, von %2$s.', 'ruhrwerk24' ),
				$rw_city['name'],
				implode( ', ', array_slice( $rw_city['parts'], 0, 4 ) )
			),
			'actions' => true,
		)
	);
	?>

	<?php foreach ( rw24_grouped_services() as $rw_index => $rw_group ) : ?>
		<section class="section<?php echo 1 === $rw_index % 2 ? ' section--grey' : ''; ?>">
			<div class="container">
				<div class="head reveal">
					<h2>
						<?php
						/* translators: 1: Leistungsgruppe, 2: Stadt. */
						echo esc_html( sprintf( __( '%1$s in %2$s', 'ruhrwerk24' ), $rw_group['term']->name, $rw_city['name'] ) );
						?>
					</h2>
					<?php if ( $rw_group['term']->description ) : ?>
						<p><?php echo esc_html( $rw_group['term']->description ); ?></p>
					<?php endif; ?>
				</div>
				<div class="grid">
					<?php
					foreach ( $rw_group['posts'] as $rw_i => $rw_post ) {
						get_template_part(
							'template-parts/service-card',
							null,
							array(
								'service' => $rw_post,
								'delay'   => $rw_i % 3 + 1,
								'url'     => rw24_city_url( $rw_post, $rw_city_slug ),
							)
						);
					}
					?>
				</div>
			</div>
		</section>
	<?php endforeach; ?>

	<section class="section section--grey">
		<div class="container container--narrow">
			<div class="head reveal">
				<h2>
					<?php
					/* translators: %s: Stadt. */
					echo esc_html( sprintf( __( 'In diesen Stadtteilen von %s sind wir unterwegs', 'ruhrwerk24' ), $rw_city['name'] ) );
					?>
				</h2>
			</div>
			<ul class="chips chips--static reveal">
				<?php foreach ( $rw_city['parts'] as $rw_part ) : ?>
					<li><span class="chip"><?php echo esc_html( $rw_part ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php get_template_part( 'template-parts/region', null, array( 'city' => $rw_city ) ); ?>

	<?php get_template_part( 'template-parts/emergency' ); ?>

	<?php
	get_template_part(
		'template-parts/areas-teaser',
		null,
		array(
			'cities' => rw24_nearby_cities( $rw_city_slug, 10 ),
			'title'  => __( 'Städte in der Nähe', 'ruhrwerk24' ),
			/* translators: %s: Region. */
			'lead'   => sprintf( __( 'Weitere Einsatzorte in der Region %s.', 'ruhrwerk24' ), $rw_city['region'] ),
		)
	);
	?>

	<?php
	get_template_part(
		'template-parts/cta',
		null,
		array(
			/* translators: %s: Stadt. */
			'title' => sprintf( __( 'Handwerker in %s gesucht? Wir sind da.', 'ruhrwerk24' ), $rw_city['name'] ),
		)
	);
	?>

</main>
<?php
get_footer();
