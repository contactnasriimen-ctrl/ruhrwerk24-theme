<?php
/**
 * Template Name: Notdienst
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Alle Leistungen, die als Notdienst markiert sind - in Anzeigereihenfolge.
$rw_emergency = array_values(
	array_filter(
		rw24_all_services(),
		static fn( WP_Post $p ): bool => '1' === get_post_meta( $p->ID, '_rw_emergency', true )
	)
);

while ( have_posts() ) :
	the_post();
	?>
	<main id="inhalt">

		<?php
		get_template_part(
			'template-parts/pagehead',
			null,
			array(
				'title'   => get_the_title(),
				'lead'    => rw24_contact( 'rw_em_text' ),
				'eyebrow' => __( '24/7 erreichbar', 'ruhrwerk24' ),
				'variant' => 'emergency',
				'actions' => true,
			)
		);
		?>

		<section class="section">
			<div class="container">
				<div class="head reveal">
					<h2><?php esc_html_e( 'Wobei wir sofort helfen', 'ruhrwerk24' ); ?></h2>
					<p><?php esc_html_e( 'Für diese Leistungen ist unser Notdienst rund um die Uhr unterwegs – auch nachts, an Wochenenden und Feiertagen.', 'ruhrwerk24' ); ?></p>
				</div>

				<?php if ( $rw_emergency ) : ?>
					<div class="grid">
						<?php
						foreach ( $rw_emergency as $rw_i => $rw_post ) {
							get_template_part( 'template-parts/service-card', null, array( 'service' => $rw_post, 'delay' => $rw_i % 3 + 1 ) );
						}
						?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<section class="section section--grey">
			<div class="container">
				<div class="head reveal">
					<h2><?php esc_html_e( 'Bis wir da sind: die wichtigsten Handgriffe', 'ruhrwerk24' ); ?></h2>
				</div>
				<ol class="steps-tips">
					<li class="reveal" data-delay="1"><strong><?php esc_html_e( 'Wasser abdrehen', 'ruhrwerk24' ); ?></strong> <?php esc_html_e( 'Den Hauptabsperrhahn finden Sie meist im Keller neben der Wasseruhr.', 'ruhrwerk24' ); ?></li>
					<li class="reveal" data-delay="2"><strong><?php esc_html_e( 'Strom sichern', 'ruhrwerk24' ); ?></strong> <?php esc_html_e( 'Steht Wasser in der Nähe von Steckdosen, die Sicherung für den Raum ausschalten.', 'ruhrwerk24' ); ?></li>
					<li class="reveal" data-delay="3"><strong><?php esc_html_e( 'Fotos machen', 'ruhrwerk24' ); ?></strong> <?php esc_html_e( 'Für die Versicherung den Schaden fotografieren, bevor aufgeräumt wird.', 'ruhrwerk24' ); ?></li>
				</ol>

				<?php if ( get_the_content() ) : ?>
					<div class="prose reveal notdienst__content"><?php the_content(); ?></div>
				<?php endif; ?>
			</div>
		</section>

		<?php get_template_part( 'template-parts/cta' ); ?>

	</main>
	<?php
endwhile;

get_footer();
