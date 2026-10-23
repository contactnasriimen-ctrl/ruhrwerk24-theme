<?php
/**
 * Einsatzgebiete als Stadt-Chips mit Link auf die Stadtübersicht.
 *
 * Argumente:
 *   cities  Stadtliste (Standard: alle)
 *   title   Überschrift
 *   lead    Einleitung
 *   service WP_Post - verlinkt dann auf Leistung × Stadt statt auf die Übersicht
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

if ( ! rw24_areas_url() ) {
	return;
}

$rw_cities  = $args['cities'] ?? rw24_cities();
$rw_service = $args['service'] ?? null;
$rw_title   = $args['title'] ?? __( 'Unsere Einsatzgebiete', 'ruhrwerk24' );
$rw_lead    = $args['lead'] ?? __( 'Vom Ruhrgebiet bis ins Rheinland: Wählen Sie Ihre Stadt.', 'ruhrwerk24' );

if ( ! $rw_cities ) {
	return;
}
?>
<section class="section areas" aria-labelledby="areas-title">
	<div class="container">
		<div class="head reveal">
			<span class="kicker"><?php echo rw24_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'NRW', 'ruhrwerk24' ); ?></span>
			<h2 id="areas-title"><?php echo esc_html( $rw_title ); ?></h2>
			<?php if ( $rw_lead ) : ?>
				<p><?php echo esc_html( $rw_lead ); ?></p>
			<?php endif; ?>
		</div>
		<ul class="chips reveal">
			<?php foreach ( $rw_cities as $rw_slug => $rw_city ) : ?>
				<li>
					<a class="chip" href="<?php echo esc_url( $rw_service ? rw24_city_url( $rw_service, $rw_slug ) : rw24_city_hub_url( $rw_slug ) ); ?>">
						<?php echo esc_html( $rw_city['name'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="areas__all reveal">
			<a class="link-arrow" href="<?php echo esc_url( rw24_areas_url() ); ?>">
				<?php esc_html_e( 'Alle Servicegebiete ansehen', 'ruhrwerk24' ); ?>
				<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</p>
	</div>
</section>
