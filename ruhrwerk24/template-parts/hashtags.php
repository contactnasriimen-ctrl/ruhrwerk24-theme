<?php
/**
 * Hashtags (als interne Links) und Teilen-Leiste.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_tags = rw24_page_hashtags();

if ( ! $rw_tags ) {
	return;
}

$rw_url   = rw24_city_canonical() ? rw24_city_canonical() : ( is_singular() ? (string) get_permalink() : home_url( '/' ) );
$rw_title = wp_get_document_title();
?>
<section class="share" aria-labelledby="share-title">
	<div class="container">
	<div class="share__inner reveal">
		<div class="share__tags">
			<h2 class="share__title" id="share-title"><?php esc_html_e( 'Hashtags', 'ruhrwerk24' ); ?></h2>
			<ul class="share__list">
				<?php foreach ( $rw_tags as $rw_tag ) : ?>
					<li>
						<?php if ( $rw_tag['url'] ) : ?>
							<a class="hashtag" href="<?php echo esc_url( $rw_tag['url'] ); ?>"><?php echo esc_html( $rw_tag['tag'] ); ?></a>
						<?php else : ?>
							<span class="hashtag"><?php echo esc_html( $rw_tag['tag'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="share__buttons">
			<p class="share__label"><?php esc_html_e( 'Seite teilen', 'ruhrwerk24' ); ?></p>
			<ul class="share__list">
				<?php foreach ( rw24_share_links( $rw_url, $rw_title, $rw_tags ) as $rw_key => $rw_link ) : ?>
					<li>
						<a class="share__btn share__btn--<?php echo esc_attr( $rw_key ); ?>" href="<?php echo esc_url( $rw_link['url'] ); ?>" target="_blank" rel="noopener nofollow">
							<?php echo rw24_icon( $rw_link['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $rw_link['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
				<li>
					<button type="button" class="share__btn share__btn--copy" data-copy="<?php echo esc_attr( $rw_url ); ?>" data-copied="<?php esc_attr_e( 'Kopiert!', 'ruhrwerk24' ); ?>">
						<?php echo rw24_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Link kopieren', 'ruhrwerk24' ); ?></span>
					</button>
				</li>
			</ul>
		</div>
	</div>
	</div>
</section>
