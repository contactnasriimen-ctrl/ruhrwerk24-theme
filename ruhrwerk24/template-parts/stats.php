<?php
/**
 * Zahlenband mit plastischen Ziffern.
 *
 * Nur Werte, die das Theme selbst kennt (Anzahl Leistungen und Städte) -
 * keine erfundenen Kundenzahlen. Weitere Kennzahlen über "rw24_stats".
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_stats = (array) apply_filters(
	'rw24_stats',
	array(
		array( 'value' => (string) count( rw24_all_services() ), 'label' => __( 'Gewerke unter einem Dach', 'ruhrwerk24' ) ),
		array( 'value' => (string) count( rw24_cities() ), 'label' => __( 'Städte in NRW', 'ruhrwerk24' ) ),
		array( 'value' => '24/7', 'label' => __( 'Notdienst, auch an Feiertagen', 'ruhrwerk24' ) ),
		array( 'value' => '1', 'label' => __( 'Ansprechpartner für alles', 'ruhrwerk24' ) ),
	)
);
?>
<section class="stats on-dark" aria-label="<?php esc_attr_e( 'Ruhrwerk24 in Zahlen', 'ruhrwerk24' ); ?>">
	<div class="bg-grid" aria-hidden="true"></div>
	<div class="container">
		<dl class="stats__list">
			<?php foreach ( $rw_stats as $rw_i => $rw_stat ) : ?>
				<?php $rw_numeric = ctype_digit( $rw_stat['value'] ); ?>
				<div class="stats__item reveal" data-delay="<?php echo esc_attr( (string) ( $rw_i % 4 + 1 ) ); ?>">
					<dt class="stats__value" data-text="<?php echo esc_attr( $rw_stat['value'] ); ?>"<?php echo $rw_numeric ? ' data-count="' . esc_attr( $rw_stat['value'] ) . '"' : ''; ?>><?php echo esc_html( $rw_stat['value'] ); ?></dt>
					<dd><?php echo esc_html( $rw_stat['label'] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>
