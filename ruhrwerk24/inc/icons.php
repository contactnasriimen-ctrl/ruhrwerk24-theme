<?php
/**
 * Inline-SVG-Icons.
 *
 * Bewusst als Inline-SVG statt Icon-Font: keine zusätzliche Datei, keine
 * Fremdquelle, und die Icons übernehmen die Textfarbe (currentColor).
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pfaddaten aller Icons im 24er-Raster.
 *
 * @return array<string, string>
 */
function rw24_icon_paths(): array {
	return array(
		'drain'    => '<path d="M4 4h16v4H4z"/><path d="M8 8v4a4 4 0 0 0 8 0V8"/><path d="M12 16v5"/><path d="M9 21h6"/>',
		'pipe'     => '<path d="M3 8h8a3 3 0 0 1 3 3v10"/><path d="M3 4h8a7 7 0 0 1 7 7v10"/><path d="M2 3v6M12 21h8"/>',
		'camera'   => '<rect x="2" y="7" width="14" height="10" rx="2"/><path d="m16 11 6-3v8l-6-3"/><circle cx="9" cy="12" r="2.5"/>',
		'droplet'  => '<path d="M12 2.7s7 7.3 7 12.3a7 7 0 0 1-14 0c0-5 7-12.3 7-12.3z"/><path d="M9 15a3 3 0 0 0 3 3"/>',
		'flame'    => '<path d="M12 22a7 7 0 0 0 7-7c0-4-3-6.5-4-10-1.5 2-2 3.5-2 5-1.5-1-2.5-2.5-2.5-4C7.5 8 5 11 5 15a7 7 0 0 0 7 7z"/><path d="M12 22a3 3 0 0 1-3-3c0-2 3-4 3-4s3 2 3 4a3 3 0 0 1-3 3z"/>',
		'bolt'     => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
		'trowel'   => '<path d="m14 4 6 6-8 8-6-6z"/><path d="M6 12 3 21l9-3"/><path d="m17 7 4-4"/>',
		'box'      => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/><path d="m7.5 5.5 9 5"/>',
		'phone'    => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
		'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
		'pin'      => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
		'clock'    => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'check'    => '<path d="M20 6 9 17l-5-5"/>',
		'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
		'euro'     => '<path d="M18.5 6.5A7 7 0 0 0 13.6 4.5a7.5 7.5 0 0 0 0 15 7 7 0 0 0 4.9-2"/><path d="M4.5 10.5h10M4.5 13.5h8"/>',
		'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M8 3v4M16 3v4M3.5 10h17"/><path d="m9 15 2 2 4-4"/>',
		'leaf'     => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10z"/><path d="M2 21c0-3 1.9-5.4 5.1-6C9.5 14.5 12 13 13 12"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'alert'    => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
		'tool'     => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9z"/>',
		'chat'     => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
		'home'     => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
		'star'     => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2L5.8 21 7 14.2 2 9.3l6.9-1z"/>',
		'truck'    => '<path d="M2 6h11v10H2z"/><path d="M13 9h4.5l3.5 3.5V16h-8"/><circle cx="6" cy="17.5" r="2"/><circle cx="17" cy="17.5" r="2"/>',
		'building' => '<path d="M4 21V5l8-3v19"/><path d="M12 9h8v12"/><path d="M8 7v.01M8 11v.01M8 15v.01M16 13v.01M16 17v.01M2 21h20"/>',
		'package'  => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/><path d="M7.5 5.5 16.5 10.5v3"/>',
	);
}

/**
 * Ein Icon als SVG-String.
 *
 * Icons sind dekorativ (aria-hidden). Wo ein Icon allein steht, muss das
 * umgebende Element ein aria-label tragen.
 */
function rw24_icon( string $name, string $class = '' ): string {
	$paths = rw24_icon_paths();

	if ( ! isset( $paths[ $name ] ) ) {
		$name = 'tool';
	}

	return sprintf(
		'<svg class="icon%s" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
		$class ? ' ' . esc_attr( $class ) : '',
		$paths[ $name ] // Statische Pfade aus dieser Datei, keine Nutzereingabe.
	);
}

/**
 * Auswahlliste für die Meta-Box der Leistungen.
 *
 * @return array<string, string>
 */
function rw24_icon_choices(): array {
	return array(
		'drain'   => __( 'Abfluss', 'ruhrwerk24' ),
		'pipe'    => __( 'Rohr', 'ruhrwerk24' ),
		'camera'  => __( 'Kamera', 'ruhrwerk24' ),
		'droplet' => __( 'Wasser', 'ruhrwerk24' ),
		'flame'   => __( 'Heizung', 'ruhrwerk24' ),
		'bolt'    => __( 'Elektro', 'ruhrwerk24' ),
		'trowel'  => __( 'Ausbau', 'ruhrwerk24' ),
		'box'      => __( 'Räumung', 'ruhrwerk24' ),
		'truck'    => __( 'Umzug / Transporter', 'ruhrwerk24' ),
		'building' => __( 'Firma / Gebäude', 'ruhrwerk24' ),
		'package'  => __( 'Paket / Transport', 'ruhrwerk24' ),
		'home'    => __( 'Haus', 'ruhrwerk24' ),
		'leaf'    => __( 'Umwelt', 'ruhrwerk24' ),
		'tool'    => __( 'Werkzeug', 'ruhrwerk24' ),
	);
}
