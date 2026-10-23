<?php
/**
 * Einsatzgebiete: Städte in NRW.
 *
 * Die Reihenfolge zählt: Die ersten acht Einträge sind die meistgefragten
 * Städte und erscheinen im Footer. Über den Filter "rw24_cities" lassen
 * sich Städte ergänzen oder entfernen, ohne diese Datei zu ändern.
 *
 * Nach Änderungen an der Liste einmal unter Einstellungen > Permalinks
 * speichern, damit die neuen Adressen erreichbar sind.
 *
 * @package Ruhrwerk24
 */

defined( 'ABSPATH' ) || exit;

/**
 * Alle Städte.
 *
 * kreis: leer bei kreisfreien Städten.
 *
 * @return array<string, array{name: string, region: string, zip: string, kreis: string, parts: string[]}>
 */
function rw24_cities(): array {
	static $cities = null;

	if ( null !== $cities ) {
		return $cities;
	}

	$cities = array(
		// Meistgefragt - erscheinen im Footer.
		'essen'                => array( 'name' => 'Essen', 'region' => 'Ruhrgebiet', 'zip' => '45127', 'kreis' => '', 'parts' => array( 'Rüttenscheid', 'Altenessen', 'Steele', 'Kettwig', 'Borbeck', 'Werden' ) ),
		'dortmund'             => array( 'name' => 'Dortmund', 'region' => 'Ruhrgebiet', 'zip' => '44135', 'kreis' => '', 'parts' => array( 'Innenstadt', 'Hörde', 'Aplerbeck', 'Hombruch', 'Brackel', 'Eving' ) ),
		'bochum'               => array( 'name' => 'Bochum', 'region' => 'Ruhrgebiet', 'zip' => '44787', 'kreis' => '', 'parts' => array( 'Wattenscheid', 'Langendreer', 'Weitmar', 'Linden', 'Gerthe' ) ),
		'duisburg'             => array( 'name' => 'Duisburg', 'region' => 'Ruhrgebiet', 'zip' => '47051', 'kreis' => '', 'parts' => array( 'Meiderich', 'Rheinhausen', 'Homberg', 'Hamborn', 'Walsum', 'Neudorf' ) ),
		'gelsenkirchen'        => array( 'name' => 'Gelsenkirchen', 'region' => 'Ruhrgebiet', 'zip' => '45879', 'kreis' => '', 'parts' => array( 'Buer', 'Erle', 'Horst', 'Schalke', 'Ückendorf' ) ),
		'oberhausen'           => array( 'name' => 'Oberhausen', 'region' => 'Ruhrgebiet', 'zip' => '46045', 'kreis' => '', 'parts' => array( 'Sterkrade', 'Osterfeld', 'Alstaden', 'Schmachtendorf' ) ),
		'duesseldorf'          => array( 'name' => 'Düsseldorf', 'region' => 'Rheinland', 'zip' => '40213', 'kreis' => '', 'parts' => array( 'Bilk', 'Oberkassel', 'Benrath', 'Gerresheim', 'Pempelfort', 'Flingern' ) ),
		'koeln'                => array( 'name' => 'Köln', 'region' => 'Rheinland', 'zip' => '50667', 'kreis' => '', 'parts' => array( 'Ehrenfeld', 'Nippes', 'Deutz', 'Sülz', 'Porz', 'Kalk' ) ),

		// Ruhrgebiet.
		'muelheim-an-der-ruhr' => array( 'name' => 'Mülheim an der Ruhr', 'region' => 'Ruhrgebiet', 'zip' => '45468', 'kreis' => '', 'parts' => array( 'Saarn', 'Speldorf', 'Heißen', 'Dümpten', 'Styrum' ) ),
		'herne'                => array( 'name' => 'Herne', 'region' => 'Ruhrgebiet', 'zip' => '44623', 'kreis' => '', 'parts' => array( 'Wanne-Eickel', 'Sodingen', 'Holsterhausen', 'Börnig' ) ),
		'bottrop'              => array( 'name' => 'Bottrop', 'region' => 'Ruhrgebiet', 'zip' => '46236', 'kreis' => '', 'parts' => array( 'Kirchhellen', 'Fuhlenbrock', 'Eigen', 'Batenbrock' ) ),
		'recklinghausen'       => array( 'name' => 'Recklinghausen', 'region' => 'Ruhrgebiet', 'zip' => '45657', 'kreis' => 'Recklinghausen', 'parts' => array( 'Suderwich', 'Hochlarmark', 'Speckhorn', 'Hillerheide' ) ),
		'gladbeck'             => array( 'name' => 'Gladbeck', 'region' => 'Ruhrgebiet', 'zip' => '45964', 'kreis' => 'Recklinghausen', 'parts' => array( 'Brauck', 'Rentfort', 'Zweckel', 'Butendorf' ) ),
		'castrop-rauxel'       => array( 'name' => 'Castrop-Rauxel', 'region' => 'Ruhrgebiet', 'zip' => '44575', 'kreis' => 'Recklinghausen', 'parts' => array( 'Ickern', 'Habinghorst', 'Henrichenburg', 'Schwerin' ) ),
		'hagen'                => array( 'name' => 'Hagen', 'region' => 'Ruhrgebiet', 'zip' => '58095', 'kreis' => '', 'parts' => array( 'Hohenlimburg', 'Haspe', 'Boele', 'Eilpe' ) ),
		'hamm'                 => array( 'name' => 'Hamm', 'region' => 'Ruhrgebiet', 'zip' => '59065', 'kreis' => '', 'parts' => array( 'Bockum-Hövel', 'Heessen', 'Rhynern', 'Pelkum' ) ),
		'witten'               => array( 'name' => 'Witten', 'region' => 'Ruhrgebiet', 'zip' => '58452', 'kreis' => 'Ennepe-Ruhr-Kreis', 'parts' => array( 'Annen', 'Herbede', 'Heven', 'Bommern' ) ),

		// Rheinland.
		'neuss'                => array( 'name' => 'Neuss', 'region' => 'Rheinland', 'zip' => '41460', 'kreis' => 'Rhein-Kreis Neuss', 'parts' => array( 'Holzheim', 'Norf', 'Grimlinghausen', 'Reuschenberg' ) ),
		'krefeld'              => array( 'name' => 'Krefeld', 'region' => 'Rheinland', 'zip' => '47798', 'kreis' => '', 'parts' => array( 'Uerdingen', 'Fischeln', 'Hüls', 'Bockum' ) ),
		'moenchengladbach'     => array( 'name' => 'Mönchengladbach', 'region' => 'Rheinland', 'zip' => '41061', 'kreis' => '', 'parts' => array( 'Rheydt', 'Odenkirchen', 'Neuwerk', 'Giesenkirchen' ) ),
		'ratingen'             => array( 'name' => 'Ratingen', 'region' => 'Rheinland', 'zip' => '40878', 'kreis' => 'Mettmann', 'parts' => array( 'Lintorf', 'Hösel', 'Breitscheid', 'West' ) ),

		// Bergisches Land.
		'wuppertal'            => array( 'name' => 'Wuppertal', 'region' => 'Bergisches Land', 'zip' => '42103', 'kreis' => '', 'parts' => array( 'Elberfeld', 'Barmen', 'Vohwinkel', 'Ronsdorf', 'Cronenberg' ) ),
		'solingen'             => array( 'name' => 'Solingen', 'region' => 'Bergisches Land', 'zip' => '42651', 'kreis' => '', 'parts' => array( 'Ohligs', 'Wald', 'Höhscheid', 'Gräfrath' ) ),
		'remscheid'            => array( 'name' => 'Remscheid', 'region' => 'Bergisches Land', 'zip' => '42853', 'kreis' => '', 'parts' => array( 'Lennep', 'Lüttringhausen', 'Hasten' ) ),

		// Niederrhein.
		'moers'                => array( 'name' => 'Moers', 'region' => 'Niederrhein', 'zip' => '47441', 'kreis' => 'Wesel', 'parts' => array( 'Repelen', 'Kapellen', 'Asberg', 'Schwafheim' ) ),
		'wesel'                => array( 'name' => 'Wesel', 'region' => 'Niederrhein', 'zip' => '46483', 'kreis' => 'Wesel', 'parts' => array( 'Büderich', 'Flüren', 'Blumenkamp', 'Obrighoven' ) ),

		// Münsterland.
		'muenster'             => array( 'name' => 'Münster', 'region' => 'Münsterland', 'zip' => '48143', 'kreis' => '', 'parts' => array( 'Hiltrup', 'Gievenbeck', 'Kinderhaus', 'Wolbeck' ) ),
	);

	$cities = (array) apply_filters( 'rw24_cities', $cities );

	return $cities;
}

/**
 * Eine Stadt nach Slug oder null.
 *
 * @return array{name: string, region: string, zip: string, kreis: string, parts: string[]}|null
 */
function rw24_city( string $slug ): ?array {
	return rw24_cities()[ $slug ] ?? null;
}

/**
 * "Kreis Wesel", "Rhein-Kreis Neuss" oder "kreisfreie Stadt".
 */
function rw24_city_district( array $city ): string {
	if ( empty( $city['kreis'] ) ) {
		return __( 'kreisfreie Stadt', 'ruhrwerk24' );
	}

	return str_contains( $city['kreis'], 'Kreis' ) ? $city['kreis'] : 'Kreis ' . $city['kreis'];
}

/**
 * Weitere Städte derselben Region, für interne Verlinkung.
 *
 * @return array<string, array>
 */
function rw24_nearby_cities( string $slug, int $limit = 6 ): array {
	$city = rw24_city( $slug );

	if ( ! $city ) {
		return array();
	}

	$same = array_filter(
		rw24_cities(),
		static fn( array $c, string $s ): bool => $s !== $slug && $c['region'] === $city['region'],
		ARRAY_FILTER_USE_BOTH
	);

	return array_slice( $same, 0, $limit, true );
}
