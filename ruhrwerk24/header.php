<?php
/**
 * Kopfbereich mit Navigation und Mobile-Drawer.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="<?php echo esc_attr( rw24_contact( 'rw_color_primary' ) ); ?>">
	<meta name="format-detection" content="telephone=no">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="scrollbar" aria-hidden="true"><span></span></div>
<?php // Feste Ebene für den 3D-Hintergrund (assets/js/bg3d.js). ?>
<div class="bg3d" aria-hidden="true"></div>

<a class="skip" href="#inhalt"><?php esc_html_e( 'Zum Inhalt springen', 'ruhrwerk24' ); ?></a>

<div class="topbar">
	<div class="container topbar__inner">
		<span class="topbar__item"><?php echo rw24_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rw24_contact( 'rw_hours' ) ); ?></span>
		<span class="topbar__item topbar__area"><?php echo rw24_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php esc_html_e( 'Im Einsatz in ganz NRW', 'ruhrwerk24' ); ?></span>
		<a class="topbar__item topbar__mail" href="mailto:<?php echo esc_attr( rw24_contact( 'rw_email' ) ); ?>"><?php echo rw24_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( rw24_contact( 'rw_email' ) ); ?></a>
	</div>
</div>

<header class="header" id="header">
	<div class="container header__inner">
		<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ' – ' . __( 'Startseite', 'ruhrwerk24' ) ); ?>">
				<span class="logo__mark" aria-hidden="true"><?php echo rw24_icon( 'tool' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span>Ruhrwerk<b>24</b></span>
			</a>
		<?php endif; ?>

		<?php
		wp_nav_menu(
			array(
				'theme_location'       => 'primary',
				'container'            => 'nav',
				'container_class'      => 'nav',
				'container_aria_label' => __( 'Hauptnavigation', 'ruhrwerk24' ),
				'menu_class'           => 'nav__list',
				'depth'                => 1,
				'fallback_cb'          => 'rw24_nav_fallback',
			)
		);
		?>

		<a class="header__phone" href="<?php echo esc_url( rw24_phone_href() ); ?>">
			<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?></span>
		</a>

		<a class="btn btn--primary header__cta" href="<?php echo esc_url( rw24_form_page_url() ); ?>">
			<?php esc_html_e( 'Anfrage senden', 'ruhrwerk24' ); ?>
		</a>

		<button class="burger" id="burger" type="button" aria-expanded="false" aria-controls="drawer" aria-label="<?php esc_attr_e( 'Menü öffnen', 'ruhrwerk24' ); ?>" data-label-open="<?php esc_attr_e( 'Menü öffnen', 'ruhrwerk24' ); ?>" data-label-close="<?php esc_attr_e( 'Menü schließen', 'ruhrwerk24' ); ?>"><span></span></button>
	</div>
</header>

<div class="drawer" id="drawer" hidden>
	<nav class="drawer__nav" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'ruhrwerk24' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'drawer__list',
				'depth'          => 1,
				'fallback_cb'    => 'rw24_nav_fallback',
			)
		);
		?>
	</nav>
	<div class="drawer__actions">
		<a class="btn btn--primary btn--wide" href="<?php echo esc_url( rw24_form_page_url() ); ?>">
			<?php esc_html_e( 'Anfrage in 60 Sekunden', 'ruhrwerk24' ); ?>
		</a>
		<a class="btn btn--outline btn--wide" href="<?php echo esc_url( rw24_phone_href() ); ?>">
			<?php echo rw24_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo esc_html( rw24_contact( 'rw_phone' ) ); ?>
		</a>
	</div>
</div>
