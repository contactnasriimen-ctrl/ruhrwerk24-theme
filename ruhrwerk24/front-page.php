<?php
/**
 * Startseite: Hero mit Anfrageformular, Versprechen, Leistungsgruppen,
 * Ablauf, Notdienst, Einsatzgebiete.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

$rw_hero_img = (int) get_theme_mod( 'rw_hero_image', 0 );
?>
<main id="inhalt">

	<section class="hero on-dark<?php echo $rw_hero_img ? ' hero--image' : ''; ?>">

		<?php if ( $rw_hero_img ) : ?>
			<div class="hero__media"><?php rw24_media( $rw_hero_img, 'hero', '', true ); ?></div>
		<?php endif; ?>

		<?php // Animierter Hintergrund: driftende Lichtflaechen und ein Raster. ?>
		<div class="bg-anim" aria-hidden="true"><span></span><span></span><span></span></div>
		<div class="bg-grid" aria-hidden="true"></div>

		<?php
		// 3D-Rohrnetz (assets/js/hero3d.js) - nur ohne eigenes Hintergrundfoto,
		// damit Foto und Szene sich nicht gegenseitig die Wirkung nehmen.
		if ( ! $rw_hero_img ) :
			?>
			<div class="scene3d" data-scene3d="hero" aria-hidden="true"></div>
		<?php endif; ?>

		<div class="container hero__inner">
			<div class="hero__layout">

				<div class="hero__content">
					<?php if ( rw24_contact( 'rw_hero_eyebrow' ) ) : ?>
						<span class="hero__eyebrow"><span class="dot" aria-hidden="true"></span> <?php echo esc_html( rw24_contact( 'rw_hero_eyebrow' ) ); ?></span>
					<?php endif; ?>

					<h1 class="split-words"><?php echo esc_html( rw24_contact( 'rw_hero_title' ) ); ?></h1>
					<p class="hero__text"><?php echo esc_html( rw24_contact( 'rw_hero_text' ) ); ?></p>

					<div class="hero__actions">
						<a class="btn btn--ghost btn--wide" href="<?php echo esc_url( rw24_phone_href() ); ?>">
							<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
						</a>
						<a class="btn btn--emergency btn--wide" href="<?php echo esc_url( home_url( '/notdienst/' ) ); ?>">
							<?php echo rw24_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Notdienst', 'ruhrwerk24' ); ?>
						</a>
					</div>

					<dl class="hero__facts">
						<?php
						for ( $rw_n = 1; $rw_n <= 3; $rw_n++ ) :
							$rw_val = rw24_contact( "rw_fact_{$rw_n}_value" );
							$rw_lab = rw24_contact( "rw_fact_{$rw_n}_label" );

							if ( ! $rw_val ) {
								continue;
							}

							// Reine Zahlen zaehlen hoch, Texte wie "24/7" bleiben stehen.
							// Der Endwert steht im HTML, damit Suchmaschinen und Nutzer
							// ohne JavaScript die richtige Zahl sehen.
							$rw_numeric = ctype_digit( $rw_val );
							?>
							<div class="fact">
								<dt<?php echo $rw_numeric ? ' data-count="' . esc_attr( $rw_val ) . '"' : ''; ?>><?php echo esc_html( $rw_val ); ?></dt>
								<dd><?php echo esc_html( $rw_lab ); ?></dd>
							</div>
						<?php endfor; ?>
					</dl>
				</div>

				<?php // Das Formular sitzt direkt im Hero - kein Klick bis zur ersten Frage. ?>
				<div class="hero__form" id="anfrage">
					<?php rw24_render_form( 'hero' ); ?>
				</div>

			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/usp' ); ?>

	<?php get_template_part( 'template-parts/service-groups' ); ?>

	<?php get_template_part( 'template-parts/process' ); ?>

	<?php get_template_part( 'template-parts/emergency' ); ?>

	<?php get_template_part( 'template-parts/areas-teaser', null, array( 'cities' => array_slice( rw24_cities(), 0, 16, true ) ) ); ?>

	<?php
	// Inhalt der Startseite aus dem Editor (optional), z. B. Über-uns-Text.
	// Nur bei statischer Startseite - sonst stünde hier die Beitragsliste.
	while ( is_page() && have_posts() ) :
		the_post();
		if ( get_the_content() ) :
			?>
			<section class="section section--grey">
				<div class="container prose reveal"><?php the_content(); ?></div>
			</section>
			<?php
		endif;
	endwhile;
	?>

	<?php get_template_part( 'template-parts/cta' ); ?>

</main>
<?php
get_footer();
