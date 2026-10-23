<?php
/**
 * Leistungsübersicht (/leistungen/), nach Gruppen sortiert.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

get_header();

$rw_count = count( rw24_all_services() );
?>
<main id="inhalt">

	<?php
	get_template_part(
		'template-parts/pagehead',
		null,
		array(
			'title' => __( 'Unsere Leistungen', 'ruhrwerk24' ),
			'lead'  => sprintf(
				/* translators: %d: Anzahl der Leistungen. */
				_n( '%d Gewerk, ein Ansprechpartner. Alles aus einer Hand, in ganz Nordrhein-Westfalen.', '%d Gewerke, ein Ansprechpartner. Alles aus einer Hand, in ganz Nordrhein-Westfalen.', $rw_count, 'ruhrwerk24' ),
				$rw_count
			),
		)
	);
	?>

	<?php get_template_part( 'template-parts/groups-nav' ); ?>

	<?php get_template_part( 'template-parts/service-groups' ); ?>

	<?php get_template_part( 'template-parts/process' ); ?>

	<?php get_template_part( 'template-parts/cta' ); ?>

</main>
<?php
get_footer();
