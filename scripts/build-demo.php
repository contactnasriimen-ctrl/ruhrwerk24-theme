<?php
/**
 * Statische Demo für GitHub Pages aus einer lokalen WordPress-Installation.
 *
 * Lädt alle Seiten (laut XML-Sitemap) samt CSS, JS, Schriften und Bildern,
 * schreibt die Adressen auf die Pages-Domain um und legt alles in docs/ ab.
 * Das Formular verschickt in der Demo nichts - es führt auf die Danke-Seite.
 *
 * Aufruf:
 *   php scripts/build-demo.php http://localhost:8124 https://USER.github.io/REPO https://github.com/USER/REPO
 */

if ( $argc < 4 ) {
	fwrite( STDERR, "Aufruf: php build-demo.php <quelle> <ziel-basis> <repo-url>\n" );
	exit( 1 );
}

[ , $src, $base, $repo ] = $argv;
$src  = rtrim( $src, '/' );
$base = rtrim( $base, '/' );
$out  = dirname( __DIR__ ) . '/docs';

/**
 * Datei holen; null bei Fehler.
 */
function fetch( string $url, ?int &$status = null ): ?string {
	$ctx  = stream_context_create( array( 'http' => array( 'ignore_errors' => true, 'timeout' => 30, 'follow_location' => 0 ) ) );
	$body = @file_get_contents( $url, false, $ctx );
	preg_match( '#HTTP/\S+ (\d+)#', $http_response_header[0] ?? '', $m );
	$status = (int) ( $m[1] ?? 0 );
	return false === $body ? null : $body;
}

/**
 * Pfad (ohne Query) auf eine Datei in docs/ abbilden.
 */
function target( string $out, string $path ): string {
	$path = parse_url( $path, PHP_URL_PATH ) ?: '/';
	if ( str_ends_with( $path, '/' ) ) {
		$path .= 'index.html';
	}
	return $out . $path;
}

function save( string $file, string $data ): void {
	if ( ! is_dir( dirname( $file ) ) ) {
		mkdir( dirname( $file ), 0777, true );
	}
	file_put_contents( $file, $data );
}

// Aufräumen.
if ( is_dir( $out ) ) {
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $out, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $it as $f ) {
		$f->isDir() ? rmdir( $f->getPathname() ) : unlink( $f->getPathname() );
	}
} else {
	mkdir( $out, 0777, true );
}

// 1. Seiten aus der Sitemap sammeln.
$pages = array( '/' );
$index = fetch( $src . '/wp-sitemap.xml' );
preg_match_all( '#<loc>([^<]+)</loc>#', (string) $index, $maps );
foreach ( $maps[1] as $map ) {
	preg_match_all( '#<loc>([^<]+)</loc>#', (string) fetch( $map ), $locs );
	foreach ( $locs[1] as $loc ) {
		$pages[] = substr( $loc, strlen( $src ) );
	}
}
$pages = array_values( array_unique( $pages ) );

// Per JavaScript nachgeladene Dateien stehen in keinem HTML-Tag - Quellen
// und gebaute 3D-Dateien (Namen mit Prüfsumme) deshalb von der Platte lesen.
$theme  = dirname( __DIR__ ) . '/ruhrwerk24';
$assets = array(
	'/wp-content/themes/ruhrwerk24/assets/js/hero3d.js',
	'/wp-content/themes/ruhrwerk24/assets/js/bg3d.js',
	'/wp-content/themes/ruhrwerk24/assets/vendor/three/build/three.module.min.js',
	'/wp-content/themes/ruhrwerk24/assets/vendor/three/jsm/environments/RoomEnvironment.js',
	'/wp-content/themes/ruhrwerk24/assets/vendor/three/LICENSE',
);
foreach ( glob( $theme . '/assets/dist/3d/*.js' ) ?: array() as $built ) {
	$assets[] = '/wp-content/themes/ruhrwerk24/assets/dist/3d/' . basename( $built );
}

$banner = '<div style="position:relative;z-index:200;background:#F4651A;color:#061629;font:600 13px/1.4 system-ui,sans-serif;text-align:center;padding:7px 12px">'
	. 'Demo des WordPress-Themes – Kontaktdaten sind Platzhalter, das Formular sendet nichts. '
	. '<a href="' . htmlspecialchars( $repo ) . '" style="color:inherit;font-weight:800">Theme auf GitHub</a></div>';

$danke = $base . '/anfrage/danke/';

$demo_js = '<script>document.addEventListener("DOMContentLoaded",function(){document.querySelectorAll("form.qf").forEach(function(f){'
	. 'f.addEventListener("submit",function(e){if(!e.defaultPrevented){e.preventDefault();location.href=' . json_encode( $danke ) . ';}});});});</script>';

/**
 * HTML für die Demo umschreiben.
 */
$rewrite_html = static function ( string $html ) use ( $src, $base, $banner, $demo_js, &$assets ): string {
	// Lokale Assets merken.
	preg_match_all( '#(?:href|src)=["\'](' . preg_quote( $src, '#' ) . '/wp-(?:content|includes)/[^"\'\s>]+)#', $html, $m );
	foreach ( $m[1] as $url ) {
		$assets[] = substr( html_entity_decode( $url ), strlen( $src ) );
	}

	// Kopfzeilen ohne Ziel in einer statischen Kopie entfernen.
	$html = preg_replace( '#<link[^>]+(?:api\.w\.org|wp-json|application/rss\+xml|EditURI|xmlrpc)[^>]*>\s*#i', '', $html );
	$html = preg_replace( '#<meta name=.robots.[^>]*>#', '<meta name="robots" content="noindex, nofollow">', $html );

	// Formular: kein Versand, sondern Danke-Seite (siehe $demo_js).
	$html = str_replace( $src . '/wp-admin/admin-post.php', '#', $html );

	$html = preg_replace( '#(<body[^>]*>)#', '$1' . $banner, $html, 1 );
	$html = str_replace( '</body>', $demo_js . '</body>', $html );

	// Adressen auf GitHub Pages.
	// Auch URL-kodiert (Teilen-Links) und JSON-maskiert.
	$html = str_replace(
		array( rawurlencode( $src ), $src, str_replace( '/', '\/', $src ) ),
		array( rawurlencode( $base ), $base, str_replace( '/', '\/', $base ) ),
		$html
	);

	return $html;
};

// 2. Seiten speichern. Zusätzlich allen internen Links folgen - die Sitemap
// von WordPress kennt z. B. das Leistungsarchiv /leistungen/ nicht.
$count = 0;
$seen  = array_fill_keys( $pages, true );
while ( $pages ) {
	$path = array_shift( $pages );
	$html = fetch( $src . $path, $status );
	if ( 200 !== $status || null === $html ) {
		fwrite( STDERR, "Übersprungen ($status): $path\n" );
		continue;
	}

	preg_match_all( '#href=["\']' . preg_quote( $src, '#' ) . '(/[^"\'\#?\s]*)["\'\#]#', $html, $links );
	foreach ( $links[1] as $link ) {
		$skip = preg_match( '#^/(wp-|feed|comments|xmlrpc)|\.[a-z0-9]{2,5}$#i', $link );
		if ( ! $skip && str_ends_with( $link, '/' ) && ! isset( $seen[ $link ] ) ) {
			$seen[ $link ] = true;
			$pages[]       = $link;
		}
	}

	save( target( $out, $path ), $rewrite_html( $html ) );
	$count++;
}

// Danke-Seite und 404.
save( $out . '/anfrage/danke/index.html', $rewrite_html( (string) fetch( $src . '/anfrage/?anfrage=ok&nr=RW-2610-0042' ) ) );
save( $out . '/404.html', $rewrite_html( (string) fetch( $src . '/diese-seite-gibt-es-nicht/' ) ) );

// 3. Assets inkl. Schriften aus CSS.
$done = array();
while ( $assets ) {
	$path = parse_url( array_shift( $assets ), PHP_URL_PATH );
	if ( isset( $done[ $path ] ) ) {
		continue;
	}
	$done[ $path ] = true;

	$data = fetch( $src . $path, $status );
	if ( 200 !== $status || null === $data ) {
		fwrite( STDERR, "Asset fehlt ($status): $path\n" );
		continue;
	}

	if ( str_ends_with( $path, '.css' ) ) {
		preg_match_all( '#url\(["\']?(?!data:|https?:)([^"\')]+)#', $data, $urls );
		foreach ( $urls[1] as $rel ) {
			$abs = dirname( $path ) . '/' . $rel;
			while ( preg_match( '#/[^/]+/\.\./#', $abs ) ) {
				$abs = preg_replace( '#/[^/]+/\.\./#', '/', $abs, 1 );
			}
			$assets[] = $abs;
		}
	}

	save( target( $out, $path ), $data );
}

// GitHub Pages: keine Jekyll-Verarbeitung.
save( $out . '/.nojekyll', '' );
save( $out . '/robots.txt', "User-agent: *\nDisallow: /\n" );

printf( "%d Seiten, %d Dateien nach %s\n", $count, count( $done ), $out );
