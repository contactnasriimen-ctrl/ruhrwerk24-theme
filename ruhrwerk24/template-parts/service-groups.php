<?php
/**
 * Alle Leistungsgruppen als Abschnitte mit Kartenraster.
 *
 * Gemeinsamer Baustein für Startseite, Leistungsarchiv und die Vorlage
 * "Leistungsübersicht" - vorher stand dieselbe Schleife dreimal im Theme.
 *
 * Argumente:
 *   grey_first  true = der erste Abschnitt ist grau hinterlegt
 *   heading     'h2' (Standard) oder 'h3'
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_grey_first = ! empty( $args['grey_first'] );

foreach ( rw24_grouped_services() as $rw_index => $rw_group ) :
	$rw_grey = ( 0 === $rw_index % 2 ) === $rw_grey_first;
	?>
	<section class="section<?php echo $rw_grey ? ' section--grey' : ''; ?>" id="<?php echo esc_attr( $rw_group['term']->slug ); ?>" aria-labelledby="<?php echo esc_attr( 'grp-' . $rw_group['term']->slug ); ?>">
		<div class="container">
			<div class="head reveal">
				<span class="kicker"><?php echo esc_html( sprintf( '%02d', $rw_index + 1 ) ); ?></span>
				<h2 id="<?php echo esc_attr( 'grp-' . $rw_group['term']->slug ); ?>"><?php echo esc_html( $rw_group['term']->name ); ?></h2>
				<?php if ( $rw_group['term']->description ) : ?>
					<p><?php echo esc_html( $rw_group['term']->description ); ?></p>
				<?php endif; ?>
			</div>
			<div class="grid">
				<?php
				foreach ( $rw_group['posts'] as $rw_i => $rw_post ) {
					get_template_part( 'template-parts/service-card', null, array( 'service' => $rw_post, 'delay' => $rw_i % 3 + 1 ) );
				}
				?>
			</div>
		</div>
	</section>
	<?php
endforeach;
