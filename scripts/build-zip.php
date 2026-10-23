<?php
/**
 * Installierbares Theme-ZIP bauen: dist/ruhrwerk24-<version>.zip
 *
 * Eigenes Skript statt Compress-Archive: Windows PowerShell 5.1 schreibt
 * Backslashes in die Pfade, und WordPress auf Linux-Servern entpackt das
 * dann als eine Datei pro Pfad statt als Ordner.
 *
 * Aufruf: php scripts/build-zip.php
 */

$root = dirname( __DIR__ ) . '/ruhrwerk24';

preg_match( '/^Version:\s*(\S+)/m', (string) file_get_contents( $root . '/style.css' ), $m );
$version = $m[1] ?? 'dev';

$dist = dirname( __DIR__ ) . '/dist';
if ( ! is_dir( $dist ) ) {
	mkdir( $dist, 0777, true );
}

$file = $dist . '/ruhrwerk24-' . $version . '.zip';
@unlink( $file );

$zip = new ZipArchive();
$zip->open( $file, ZipArchive::CREATE );

$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::SELF_FIRST );
foreach ( $it as $f ) {
	$rel = 'ruhrwerk24/' . str_replace( '\\', '/', substr( $f->getPathname(), strlen( $root ) + 1 ) );
	$f->isDir() ? $zip->addEmptyDir( $rel ) : $zip->addFile( $f->getPathname(), $rel );
}

$zip->close();
printf( "%s (%d KB)\n", $file, filesize( $file ) / 1024 );
