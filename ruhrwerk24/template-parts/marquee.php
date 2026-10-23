<?php
/**
 * Laufband mit Leistungen und Versprechen.
 *
 * Die Liste steht zweimal im HTML, damit die Animation nahtlos läuft;
 * die Kopie ist für Screenreader ausgeblendet. Bei "Bewegung reduzieren"
 * steht das Band still und lässt sich seitlich scrollen.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_items = wp_list_pluck( rw24_all_services(), 'post_title' );
array_splice( $rw_items, 2, 0, array( __( 'Festpreis vorab', 'ruhrwerk24' ) ) );
array_splice( $rw_items, 5, 0, array( __( 'Notdienst 24/7', 'ruhrwerk24' ) ) );
$rw_items[] = __( 'Ganz NRW', 'ruhrwerk24' );

$rw_items = (array) apply_filters( 'rw24_marquee_items', $rw_items );

if ( ! $rw_items ) {
	return;
}

$rw_list = static function ( bool $hidden ) use ( $rw_items ): void {
	echo '<ul class="marquee__list"' . ( $hidden ? ' aria-hidden="true"' : '' ) . '>';
	foreach ( $rw_items as $rw_item ) {
		echo '<li>' . esc_html( $rw_item ) . '<span class="marquee__sep" aria-hidden="true">' . rw24_icon( 'star' ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</ul>';
};
?>
<section class="marquee" aria-label="<?php esc_attr_e( 'Unsere Leistungen im Überblick', 'ruhrwerk24' ); ?>">
	<div class="marquee__track">
		<?php $rw_list( false ); ?>
		<?php $rw_list( true ); ?>
	</div>
</section>
