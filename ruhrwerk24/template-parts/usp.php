<?php
/**
 * Leiste mit den vier Leistungsversprechen.
 *
 * Über den Filter "rw24_usps" anpassbar.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_usps = apply_filters(
	'rw24_usps',
	array(
		array( 'icon' => 'euro', 'title' => __( 'Festpreis vorab', 'ruhrwerk24' ), 'text' => __( 'Sie wissen, was es kostet, bevor wir anfangen.', 'ruhrwerk24' ) ),
		array( 'icon' => 'calendar', 'title' => __( 'Schnelle Termine', 'ruhrwerk24' ), 'text' => __( 'Besichtigung meist innerhalb von 48 Stunden.', 'ruhrwerk24' ) ),
		array( 'icon' => 'shield', 'title' => __( 'Meisterbetrieb', 'ruhrwerk24' ), 'text' => __( 'Eigene Fachkräfte, versichert und geprüft.', 'ruhrwerk24' ) ),
		array( 'icon' => 'leaf', 'title' => __( 'Sauber hinterlassen', 'ruhrwerk24' ), 'text' => __( 'Wir räumen auf und entsorgen fachgerecht.', 'ruhrwerk24' ) ),
	)
);
?>
<section class="usp" aria-label="<?php esc_attr_e( 'Unsere Versprechen', 'ruhrwerk24' ); ?>">
	<div class="container">
		<ul class="usp__list">
			<?php foreach ( $rw_usps as $rw_i => $rw_usp ) : ?>
				<li class="usp__item reveal" data-delay="<?php echo esc_attr( (string) ( $rw_i % 4 + 1 ) ); ?>">
					<span class="usp__icon"><?php echo rw24_icon( $rw_usp['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span>
						<strong><?php echo esc_html( $rw_usp['title'] ); ?></strong>
						<?php echo esc_html( $rw_usp['text'] ); ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
