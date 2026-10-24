<?php
/**
 * Stadt-Landingpage: eine Leistung in einer Stadt, z. B. /rohrreinigung-essen/.
 *
 * Damit die Seiten für Google nicht austauschbar wirken, bekommt jede
 * eigene Angaben: Stadtteile, PLZ, Kreis, Nachbarstädte und lokale Fragen.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_city      = rw24_current_city();
$rw_city_slug = rw24_current_city_slug();

get_header();

while ( have_posts() ) :
	the_post();

	$rw_id        = get_the_ID();
	$rw_service   = get_the_title();
	$rw_emergency = '1' === get_post_meta( $rw_id, '_rw_emergency', true );
	$rw_parts     = $rw_city['parts'];
	$rw_last_part = array_pop( $rw_parts );
	$rw_parts_txt = $rw_parts ? implode( ', ', $rw_parts ) . ' ' . __( 'und', 'ruhrwerk24' ) . ' ' . $rw_last_part : $rw_last_part;

	$rw_intro = sprintf(
		/* translators: 1: Leistung, 2: Stadt, 3: Stadtteile. */
		__( 'Sie suchen einen Fachbetrieb für %1$s in %2$s? Wir sind in allen Stadtteilen im Einsatz – unter anderem in %3$s. Sie bekommen einen festen Ansprechpartner und einen verbindlichen Preis, bevor wir anfangen.', 'ruhrwerk24' ),
		$rw_service,
		$rw_city['name'],
		$rw_parts_txt
	);

	$rw_facts = array(
		__( 'Einsatzort', 'ruhrwerk24' ) => $rw_city['name'],
		__( 'PLZ-Bereich', 'ruhrwerk24' ) => $rw_city['zip'] . ' ' . __( 'und Umgebung', 'ruhrwerk24' ),
		__( 'Gebiet', 'ruhrwerk24' )      => rw24_city_district( $rw_city ) . ', ' . $rw_city['region'],
		__( 'Erreichbar', 'ruhrwerk24' )  => $rw_emergency ? __( 'Rund um die Uhr, auch am Wochenende', 'ruhrwerk24' ) : rw24_contact( 'rw_hours' ),
	);

	// Lokale Fragen vor den allgemeinen Fragen der Leistung.
	$rw_faq = array_merge(
		array(
			array(
				/* translators: 1: Leistung, 2: Stadt. */
				'q' => sprintf( __( 'Was kostet %1$s in %2$s?', 'ruhrwerk24' ), $rw_service, $rw_city['name'] ),
				'a' => __( 'Das hängt vom Aufwand ab. Sie erhalten vor Arbeitsbeginn einen Festpreis – ohne Anfahrtspauschale im Kleingedruckten. Für eine erste Einschätzung reichen oft ein paar Fotos über unser Anfrageformular.', 'ruhrwerk24' ),
			),
			array(
				/* translators: 1: Stadtteil, 2: Stadt. */
				'q' => sprintf( __( 'Kommen Sie auch nach %1$s und in die anderen Stadtteile von %2$s?', 'ruhrwerk24' ), $rw_last_part, $rw_city['name'] ),
				/* translators: 1: Stadt, 2: Stadtteile. */
				'a' => sprintf( __( 'Ja. Wir arbeiten in ganz %1$s, unter anderem in %2$s – und in den Nachbarstädten.', 'ruhrwerk24' ), $rw_city['name'], implode( ', ', $rw_city['parts'] ) ),
			),
		),
		rw24_service_faq( $rw_id )
	);

	// Andere Leistungen in derselben Stadt.
	$rw_others = array_values( array_filter( rw24_all_services(), static fn( WP_Post $p ): bool => $p->ID !== $rw_id ) );
	?>

	<main id="inhalt">

		<?php
		get_template_part(
			'template-parts/service-hero',
			null,
			array(
				/* translators: 1: Leistung, 2: Stadt. */
				'title' => sprintf( __( '%1$s in %2$s', 'ruhrwerk24' ), $rw_service, $rw_city['name'] ),
				'lead'  => (string) get_post_meta( $rw_id, '_rw_short_claim', true ),
			)
		);
		?>

		<?php get_template_part( 'template-parts/service-body', null, array( 'intro' => $rw_intro, 'facts' => $rw_facts ) ); ?>

		<?php get_template_part( 'template-parts/region', null, array( 'city' => $rw_city ) ); ?>

		<?php
		get_template_part(
			'template-parts/faq',
			null,
			array(
				'items' => $rw_faq,
				/* translators: %s: Stadt. */
				'title' => sprintf( __( 'Fragen aus %s', 'ruhrwerk24' ), $rw_city['name'] ),
			)
		);
		?>

		<?php if ( $rw_emergency ) : ?>
			<?php get_template_part( 'template-parts/emergency' ); ?>
		<?php endif; ?>

		<?php if ( $rw_others ) : ?>
			<section class="section">
				<div class="container">
					<div class="head reveal">
						<h2>
							<?php
							/* translators: %s: Stadt. */
							echo esc_html( sprintf( __( 'Weitere Leistungen in %s', 'ruhrwerk24' ), $rw_city['name'] ) );
							?>
						</h2>
					</div>
					<div class="grid">
						<?php
						foreach ( array_slice( $rw_others, 0, 6 ) as $rw_i => $rw_post ) {
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
		<?php endif; ?>

		<?php
		get_template_part(
			'template-parts/areas-teaser',
			null,
			array(
				'service' => get_post(),
				'cities'  => rw24_nearby_cities( $rw_city_slug, 10 ),
				/* translators: %s: Leistung. */
				'title'   => sprintf( __( '%s in der Nähe', 'ruhrwerk24' ), $rw_service ),
				/* translators: %s: Region. */
				'lead'    => sprintf( __( 'Auch in diesen Städten der Region %s sind wir für Sie da.', 'ruhrwerk24' ), $rw_city['region'] ),
			)
		);
		?>

		<?php get_template_part( 'template-parts/hashtags' ); ?>

		<?php
		get_template_part(
			'template-parts/cta',
			null,
			array(
				/* translators: 1: Leistung, 2: Stadt. */
				'title' => sprintf( __( '%1$s in %2$s – jetzt anfragen.', 'ruhrwerk24' ), $rw_service, $rw_city['name'] ),
			)
		);
		?>

	</main>

	<?php
endwhile;

get_footer();
