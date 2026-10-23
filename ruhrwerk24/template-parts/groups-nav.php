<?php
/**
 * Sprungnavigation zu den Leistungsgruppen.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

$rw_groups = rw24_grouped_services();

if ( count( $rw_groups ) < 2 ) {
	return;
}
?>
<nav class="subnav" aria-label="<?php esc_attr_e( 'Leistungsgruppen', 'ruhrwerk24' ); ?>">
	<div class="container">
		<ul>
			<?php foreach ( $rw_groups as $rw_group ) : ?>
				<li><a href="#<?php echo esc_attr( $rw_group['term']->slug ); ?>"><?php echo esc_html( $rw_group['term']->name ); ?> <span><?php echo esc_html( (string) count( $rw_group['posts'] ) ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
