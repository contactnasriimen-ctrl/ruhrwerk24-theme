<?php
/**
 * Ablauf in drei Schritten.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_steps = array(
	array( 'icon' => 'chat', 'title' => __( 'Anfrage senden', 'ruhrwerk24' ), 'text' => __( 'Acht kurze Fragen online oder ein Anruf. Wir melden uns meist innerhalb weniger Stunden.', 'ruhrwerk24' ) ),
	array( 'icon' => 'euro', 'title' => __( 'Festpreis erhalten', 'ruhrwerk24' ), 'text' => __( 'Nach Fotos oder kurzer Besichtigung bekommen Sie ein verbindliches Angebot – ohne Kleingedrucktes.', 'ruhrwerk24' ) ),
	array( 'icon' => 'check', 'title' => __( 'Erledigt', 'ruhrwerk24' ), 'text' => __( 'Wir arbeiten zum vereinbarten Termin, räumen auf und übergeben mit Protokoll.', 'ruhrwerk24' ) ),
);
?>
<section class="section process" aria-labelledby="process-title">
	<div class="container">
		<div class="head reveal">
			<span class="kicker"><?php esc_html_e( 'So läuft es ab', 'ruhrwerk24' ); ?></span>
			<h2 id="process-title"><?php esc_html_e( 'In drei Schritten zum Ergebnis', 'ruhrwerk24' ); ?></h2>
		</div>
		<ol class="process__list">
			<?php foreach ( $rw_steps as $rw_i => $rw_step ) : ?>
				<li class="process__step reveal" data-delay="<?php echo esc_attr( (string) ( $rw_i + 1 ) ); ?>">
					<span class="process__icon"><?php echo rw24_icon( $rw_step['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h3><?php echo esc_html( $rw_step['title'] ); ?></h3>
					<p><?php echo esc_html( $rw_step['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
