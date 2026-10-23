<?php
/**
 * Regionaler Absatz auf Stadtseiten: typische Bausubstanz und Probleme.
 *
 * Argumente:
 *   city  Stadtdaten aus rw24_cities()
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_city = $args['city'] ?? null;
$rw_text = $rw_city ? rw24_region_text( $rw_city['region'], $rw_city['name'] ) : null;

if ( ! $rw_text ) {
	return;
}
?>
<section class="section region" aria-labelledby="region-title">
	<div class="container">
		<div class="region__inner reveal">
			<div class="region__badge" aria-hidden="true">
				<?php echo rw24_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $rw_city['region'] ); ?></span>
			</div>
			<div>
				<h2 id="region-title"><?php echo esc_html( $rw_text['title'] ); ?></h2>
				<p><?php echo esc_html( $rw_text['text'] ); ?></p>
			</div>
		</div>
	</div>
</section>
