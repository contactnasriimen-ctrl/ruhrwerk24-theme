<?php
/**
 * "Warum Ruhrwerk24" als Bento-Raster: eine große Kachel, fünf kleine.
 *
 * Über den Filter "rw24_why" anpassbar.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_items = (array) apply_filters(
	'rw24_why',
	array(
		array(
			'icon'  => 'tool',
			'title' => __( 'Ein Ansprechpartner für alles', 'ruhrwerk24' ),
			'text'  => __( 'Rohr verstopft, Bad undicht, Fliesen kaputt: Bei anderen sind das drei Firmen, drei Termine, drei Rechnungen. Bei uns ist es ein Anruf. Ihr Ansprechpartner koordiniert alle Gewerke, hält den Zeitplan und ist erreichbar, wenn Sie eine Frage haben.', 'ruhrwerk24' ),
			'size'  => 'lg',
		),
		array(
			'icon'  => 'euro',
			'title' => __( 'Festpreis vor Arbeitsbeginn', 'ruhrwerk24' ),
			'text'  => __( 'Sie wissen, was es kostet, bevor jemand anfängt. Ohne Anfahrtspauschale im Kleingedruckten.', 'ruhrwerk24' ),
		),
		array(
			'icon'  => 'clock',
			'title' => __( 'Notdienst, der wirklich kommt', 'ruhrwerk24' ),
			'text'  => __( 'Rund um die Uhr erreichbar – mit einer Ankunftszeit, auf die Sie sich verlassen können.', 'ruhrwerk24' ),
		),
		array(
			'icon'  => 'camera',
			'title' => __( 'Moderne Technik', 'ruhrwerk24' ),
			'text'  => __( 'Kanalkamera, Leckortung, Wärmebild: Wir öffnen nur dort, wo es nötig ist.', 'ruhrwerk24' ),
		),
		array(
			'icon'  => 'leaf',
			'title' => __( 'Sauber und rücksichtsvoll', 'ruhrwerk24' ),
			'text'  => __( 'Schuhüberzieher, Abdeckfolie, Staubschutz – und am Ende wird aufgeräumt.', 'ruhrwerk24' ),
		),
		array(
			'icon'  => 'shield',
			'title' => __( 'Dokumentiert für die Versicherung', 'ruhrwerk24' ),
			'text'  => __( 'Fotos, Messwerte und Bericht – damit die Regulierung reibungslos läuft.', 'ruhrwerk24' ),
		),
	)
);
?>
<section class="section why" aria-labelledby="why-title">
	<div class="container">
		<div class="head reveal">
			<span class="kicker"><?php esc_html_e( 'Warum Ruhrwerk24', 'ruhrwerk24' ); ?></span>
			<h2 id="why-title"><?php esc_html_e( 'Handwerk, wie es sein sollte', 'ruhrwerk24' ); ?></h2>
			<p><?php esc_html_e( 'Pünktlich, sauber, ehrlich im Preis. Klingt selbstverständlich – ist es leider nicht. Das macht bei uns den Unterschied:', 'ruhrwerk24' ); ?></p>
		</div>

		<div class="bento">
			<?php foreach ( $rw_items as $rw_i => $rw_item ) : ?>
				<article class="bento__item<?php echo ! empty( $rw_item['size'] ) ? ' bento__item--' . esc_attr( $rw_item['size'] ) : ''; ?> reveal" data-delay="<?php echo esc_attr( (string) ( $rw_i % 3 + 1 ) ); ?>">
					<span class="bento__icon"><?php echo rw24_icon( $rw_item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h3><?php echo esc_html( $rw_item['title'] ); ?></h3>
					<p><?php echo esc_html( $rw_item['text'] ); ?></p>
					<?php if ( ! empty( $rw_item['size'] ) ) : ?>
						<a class="link-arrow" href="<?php echo esc_url( rw24_form_page_url() ); ?>">
							<?php esc_html_e( 'Jetzt anfragen', 'ruhrwerk24' ); ?>
							<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
