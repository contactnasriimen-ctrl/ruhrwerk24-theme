<?php
/**
 * Über uns: Text aus dem Customizer, daneben ein 3D-Karussell der
 * Leistungsgruppen. Die vordere Karte ist vollständig lesbar, die übrigen
 * stehen gedreht daneben; Pfeile, Punkte, Wischen und Klick holen sie nach
 * vorn. Ohne JavaScript stehen die Karten untereinander.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_paragraphs = array_filter( array_map( 'trim', preg_split( '/\R\s*\R/', rw24_contact( 'rw_about_text' ) ) ) );
$rw_groups     = rw24_grouped_services();
$rw_total      = count( $rw_groups );
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
			<div class="deck" data-deck role="region" aria-roledescription="<?php esc_attr_e( 'Karussell', 'ruhrwerk24' ); ?>" aria-label="<?php esc_attr_e( 'Unsere Leistungsbereiche', 'ruhrwerk24' ); ?>">
				<div class="deck__stage">
					<?php foreach ( $rw_groups as $rw_i => $rw_group ) : ?>
						<?php $rw_first = $rw_group['posts'][0]; ?>
						<article class="deck__card" data-deck-card role="group" aria-roledescription="<?php esc_attr_e( 'Karte', 'ruhrwerk24' ); ?>" aria-label="<?php echo esc_attr( sprintf( '%d / %d: %s', $rw_i + 1, $rw_total, $rw_group['term']->name ) ); ?>">
							<div class="deck__top">
								<span class="deck__icon"><?php echo rw24_icon( rw24_service_icon( $rw_first->ID ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="deck__num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $rw_i + 1 ) ); ?></span>
							</div>
							<h3 class="deck__title"><?php echo esc_html( $rw_group['term']->name ); ?></h3>
							<?php if ( $rw_group['term']->description ) : ?>
								<p class="deck__text"><?php echo esc_html( $rw_group['term']->description ); ?></p>
							<?php endif; ?>
							<ul class="deck__services">
								<?php foreach ( $rw_group['posts'] as $rw_service ) : ?>
									<li><a href="<?php echo esc_url( get_permalink( $rw_service ) ); ?>"><?php echo esc_html( get_the_title( $rw_service ) ); ?></a></li>
								<?php endforeach; ?>
							</ul>
							<a class="deck__more" href="<?php echo esc_url( get_term_link( $rw_group['term'] ) ); ?>">
								<?php esc_html_e( 'Zum Bereich', 'ruhrwerk24' ); ?>
								<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</article>
					<?php endforeach; ?>
				</div>

				<?php if ( $rw_total > 1 ) : ?>
					<div class="deck__nav" hidden>
						<button type="button" class="deck__btn" data-deck-prev aria-label="<?php esc_attr_e( 'Vorherige Karte', 'ruhrwerk24' ); ?>">
							<?php echo rw24_icon( 'arrow', 'icon--flip' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
						<div class="deck__dots">
							<?php foreach ( $rw_groups as $rw_i => $rw_group ) : ?>
								<button type="button" class="deck__dot" data-deck-dot="<?php echo esc_attr( (string) $rw_i ); ?>" aria-label="<?php echo esc_attr( $rw_group['term']->name ); ?>"></button>
							<?php endforeach; ?>
						</div>
						<button type="button" class="deck__btn" data-deck-next aria-label="<?php esc_attr_e( 'Nächste Karte', 'ruhrwerk24' ); ?>">
							<?php echo rw24_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
