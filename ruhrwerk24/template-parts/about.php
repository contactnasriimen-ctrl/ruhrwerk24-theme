<?php
/**
 * Über uns: Text aus dem Customizer, daneben ein 3D-Kartenstapel der
 * drei Leistungsgruppen, der sich beim Scrollen auffächert.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_paragraphs = array_filter( array_map( 'trim', preg_split( '/\R\s*\R/', rw24_contact( 'rw_about_text' ) ) ) );
$rw_groups     = array_slice( rw24_grouped_services(), 0, 3 );
?>
<section class="section about" aria-labelledby="about-title">
	<div class="container about__inner">
		<div class="about__text reveal">
			<span class="kicker"><?php esc_html_e( 'Über uns', 'ruhrwerk24' ); ?></span>
			<h2 id="about-title"><?php echo esc_html( rw24_contact( 'rw_about_title' ) ); ?></h2>
			<?php foreach ( $rw_paragraphs as $rw_p ) : ?>
				<p><?php echo esc_html( $rw_p ); ?></p>
			<?php endforeach; ?>
			<ul class="checklist about__list">
				<li><?php echo rw24_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Fest angestellte Fachkräfte statt Subunternehmer-Ketten', 'ruhrwerk24' ); ?></li>
				<li><?php echo rw24_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Schriftliches Angebot und verbindlicher Zeitplan', 'ruhrwerk24' ); ?></li>
				<li><?php echo rw24_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Rückmeldung, wenn sich etwas ändert – nicht erst danach', 'ruhrwerk24' ); ?></li>
			</ul>
		</div>

		<?php if ( $rw_groups ) : ?>
			<div class="stack" aria-hidden="true">
				<?php foreach ( $rw_groups as $rw_i => $rw_group ) : ?>
					<?php $rw_first = $rw_group['posts'][0]; ?>
					<div class="stack__card" style="--n:<?php echo esc_attr( (string) $rw_i ); ?>">
						<span class="stack__icon"><?php echo rw24_icon( rw24_service_icon( $rw_first->ID ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="stack__num"><?php echo esc_html( sprintf( '%02d', $rw_i + 1 ) ); ?></span>
						<strong><?php echo esc_html( $rw_group['term']->name ); ?></strong>
						<span class="stack__items"><?php echo esc_html( implode( ' · ', wp_list_pluck( $rw_group['posts'], 'post_title' ) ) ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
