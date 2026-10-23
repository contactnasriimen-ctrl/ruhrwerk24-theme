<?php
/**
 * Dunkler Seitenkopf mit animiertem Hintergrund.
 *
 * Ein Baustein für alle Unterseiten, damit Abstände, Breadcrumbs und
 * Hintergrund überall gleich sind.
 *
 * Argumente:
 *   title    Überschrift (Pflicht)
 *   lead     Einleitung
 *   eyebrow  kleiner Hinweis über der Überschrift
 *   variant  '' oder 'emergency'
 *   actions  true = Telefon- und Anfrage-Button
 *   meta     Zeile unter der Überschrift (z. B. Datum)
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_args = wp_parse_args(
	$args ?? array(),
	array(
		'title'   => '',
		'lead'    => '',
		'eyebrow' => '',
		'variant' => '',
		'actions' => false,
		'meta'    => '',
	)
);

$rw_emergency = 'emergency' === $rw_args['variant'];
?>
<section class="pagehead on-dark<?php echo $rw_emergency ? ' pagehead--emergency' : ''; ?>">
	<div class="bg-anim" aria-hidden="true"><span></span><span></span><span></span></div>
	<div class="bg-grid" aria-hidden="true"></div>
	<div class="scene3d" data-scene3d="page" aria-hidden="true"></div>

	<div class="container pagehead__inner">
		<?php rw24_breadcrumbs(); ?>

		<?php if ( $rw_args['eyebrow'] ) : ?>
			<span class="<?php echo $rw_emergency ? 'emergency__tag' : 'eyebrow'; ?>">
				<?php if ( $rw_emergency ) : ?><span class="dot" aria-hidden="true"></span><?php endif; ?>
				<?php echo esc_html( $rw_args['eyebrow'] ); ?>
			</span>
		<?php endif; ?>

		<h1 class="split-words"><?php echo esc_html( $rw_args['title'] ); ?></h1>

		<?php if ( $rw_args['meta'] ) : ?>
			<p class="pagehead__meta"><?php echo esc_html( $rw_args['meta'] ); ?></p>
		<?php endif; ?>

		<?php if ( $rw_args['lead'] ) : ?>
			<p class="lead"><?php echo esc_html( $rw_args['lead'] ); ?></p>
		<?php endif; ?>

		<?php if ( $rw_args['actions'] ) : ?>
			<div class="hero__actions">
				<a class="btn <?php echo $rw_emergency ? 'btn--emergency pulse' : 'btn--primary'; ?> btn--wide" href="<?php echo esc_url( rw24_phone_href() ); ?>">
					<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
				</a>
				<a class="btn btn--ghost btn--wide" href="<?php echo esc_url( rw24_form_page_url() ); ?>">
					<?php esc_html_e( 'Anfrage senden', 'ruhrwerk24' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
