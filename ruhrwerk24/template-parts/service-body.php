<?php
/**
 * Inhalt einer Leistung: Text, Merkmale, Kontaktbox.
 *
 * Muss innerhalb des Loops stehen.
 *
 * Argumente:
 *   intro  zusätzlicher Absatz vor dem Inhalt (Stadtseiten)
 *   facts  Zeilen für die Infobox, array( Label => Wert )
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_id       = get_the_ID();
$rw_features = rw24_service_features( $rw_id );
$rw_intro    = $args['intro'] ?? '';
$rw_facts    = $args['facts'] ?? array();
?>
<section class="section">
	<div class="container service-body">

		<div class="prose reveal">
			<?php if ( $rw_intro ) : ?>
				<p class="prose__intro"><?php echo esc_html( $rw_intro ); ?></p>
			<?php endif; ?>

			<?php
			if ( get_the_content() ) {
				the_content();
			} elseif ( current_user_can( 'edit_post', $rw_id ) ) {
				printf(
					'<p class="notice">%s</p>',
					esc_html__( 'Für diese Leistung ist noch kein Text hinterlegt. Inhalt im Backend unter Leistungen ergänzen.', 'ruhrwerk24' )
				);
			}
			?>
		</div>

		<aside class="service-aside">
			<?php if ( rw24_is_transport( $rw_id ) ) : ?>
				<?php // 3D-Transporter neben dem Text (auf dem Handy steht er schon im Seitenkopf). ?>
				<div class="panel panel--stage reveal" aria-hidden="true">
					<div class="scene3d scene3d--stage" data-scene3d="truck" data-scene3d-desktop></div>
				</div>
			<?php endif; ?>

			<?php if ( $rw_features ) : ?>
				<div class="panel reveal">
					<h2 class="panel__title"><?php esc_html_e( 'Das ist inklusive', 'ruhrwerk24' ); ?></h2>
					<ul class="checklist">
						<?php foreach ( $rw_features as $rw_feature ) : ?>
							<li><?php echo rw24_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $rw_feature ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( $rw_facts ) : ?>
				<div class="panel reveal">
					<dl class="facts">
						<?php foreach ( $rw_facts as $rw_label => $rw_value ) : ?>
							<div><dt><?php echo esc_html( $rw_label ); ?></dt><dd><?php echo esc_html( $rw_value ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				</div>
			<?php endif; ?>

			<div class="panel panel--dark reveal">
				<h2 class="panel__title"><?php esc_html_e( 'Lieber direkt sprechen?', 'ruhrwerk24' ); ?></h2>
				<p><?php echo esc_html( rw24_contact( 'rw_hours' ) ); ?></p>
				<a class="btn btn--primary btn--wide" href="<?php echo esc_url( rw24_phone_href() ); ?>">
					<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
				</a>
			</div>
		</aside>

	</div>
</section>
