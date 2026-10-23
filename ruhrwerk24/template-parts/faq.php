<?php
/**
 * Häufige Fragen als aufklappbare Liste.
 *
 * <details> braucht kein JavaScript und ist für Tastatur und Screenreader
 * von Haus aus zugänglich.
 *
 * Argumente:
 *   items  array( array( 'q' => ..., 'a' => ... ) )
 *   title  Überschrift
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_items = $args['items'] ?? array();

if ( ! $rw_items ) {
	return;
}

$rw_title = $args['title'] ?? __( 'Häufige Fragen', 'ruhrwerk24' );
?>
<section class="section section--grey" aria-labelledby="faq-title">
	<div class="container container--narrow">
		<div class="head reveal">
			<h2 id="faq-title"><?php echo esc_html( $rw_title ); ?></h2>
		</div>
		<div class="faq reveal">
			<?php foreach ( $rw_items as $rw_item ) : ?>
				<details class="faq__item">
					<summary><?php echo esc_html( $rw_item['q'] ); ?></summary>
					<p><?php echo esc_html( $rw_item['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
