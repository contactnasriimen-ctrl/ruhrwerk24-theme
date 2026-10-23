<?php
/**
 * Karte einer Leistung.
 *
 * Die ganze Karte ist klickbar (über den gestreckten Link im Titel),
 * bleibt aber ein einziger Link - Screenreader lesen ihn nur einmal vor.
 *
 * Argumente:
 *   service  WP_Post der Leistung (Pflicht)
 *   delay    1-3, versetztes Einblenden im Raster
 *   url      abweichendes Ziel, z. B. die Stadtseite der Leistung
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_service = $args['service'] ?? null;

if ( ! $rw_service instanceof WP_Post ) {
	return;
}

$rw_delay     = (int) ( $args['delay'] ?? 1 );
$rw_url       = $args['url'] ?? get_permalink( $rw_service );
$rw_image     = rw24_service_image( $rw_service->ID, 'card' );
$rw_claim     = (string) get_post_meta( $rw_service->ID, '_rw_short_claim', true );
$rw_emergency = '1' === get_post_meta( $rw_service->ID, '_rw_emergency', true );
$rw_text      = has_excerpt( $rw_service ) ? get_the_excerpt( $rw_service ) : $rw_claim;
?>
<article class="card reveal" data-delay="<?php echo esc_attr( (string) $rw_delay ); ?>">

	<?php if ( $rw_image ) : ?>
		<?php rw24_media( $rw_image, 'card', get_the_title( $rw_service ) ); ?>
	<?php endif; ?>

	<div class="card__body">
		<div class="card__top">
			<span class="card__icon"><?php echo rw24_icon( rw24_service_icon( $rw_service->ID ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php if ( $rw_emergency ) : ?>
				<span class="badge badge--emergency"><?php esc_html_e( '24/7', 'ruhrwerk24' ); ?></span>
			<?php endif; ?>
		</div>

		<h3 class="card__title">
			<a href="<?php echo esc_url( $rw_url ); ?>"><?php echo esc_html( get_the_title( $rw_service ) ); ?></a>
		</h3>

		<?php if ( $rw_text ) : ?>
			<p class="card__text"><?php echo esc_html( $rw_text ); ?></p>
		<?php endif; ?>

		<span class="card__more" aria-hidden="true">
			<?php esc_html_e( 'Mehr erfahren', 'ruhrwerk24' ); ?>
			<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
	</div>
</article>
