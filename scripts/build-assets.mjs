/**
 * Baut die ausgelieferten Assets nach ruhrwerk24/assets/dist/:
 *
 *   app.css        fonts + tokens + main, gebündelt und minifiziert
 *   main.js        minifiziert
 *   3d/hero3d.js   3D-Szenen
 *   3d/bg3d.js     3D-Hintergrund
 *   3d/chunk-*.js  gemeinsam genutzter, verschlankter Teil von Three.js
 *
 * Three.js wird dabei per Tree-Shaking auf die tatsächlich genutzten Teile
 * reduziert. Das Theme lädt automatisch dist/, sobald es existiert
 * (außer bei SCRIPT_DEBUG); die Quellen in assets/css und assets/js bleiben
 * zum Bearbeiten erhalten.
 *
 * Aufruf:  node scripts/build-assets.mjs
 * esbuild: über ESBUILD=<pfad> oder im PATH.
 */
import { execFileSync } from 'node:child_process';
import { existsSync, rmSync, statSync, readdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..', 'ruhrwerk24', 'assets');
const dist = join(root, 'dist');
const esbuild = process.env.ESBUILD || 'esbuild';

const run = (args) => execFileSync(esbuild, args, { stdio: 'inherit', shell: process.platform === 'win32' });

rmSync(dist, { recursive: true, force: true });

// CSS: Schriften bleiben extern, der relative Pfad ../fonts/ stimmt auch aus dist/.
run([join(root, 'css', 'app.css'), '--bundle', '--minify', '--external:*.woff2', `--outfile=${join(dist, 'app.css')}`, '--log-level=warning']);

// Seiten-Script (klassisches Script, kein Modul).
run([join(root, 'js', 'main.js'), '--minify', '--target=es2019', `--outfile=${join(dist, 'main.js')}`, '--log-level=warning']);

// 3D: zwei Einstiegspunkte, gemeinsamer Three.js-Teil als Chunk.
run([join(root, 'js', 'hero3d.js'), join(root, 'js', 'bg3d.js'), '--bundle', '--splitting', '--format=esm', '--minify', `--outdir=${join(dist, '3d')}`, '--log-level=warning']);

// Übersicht der Größen.
const sizes = (dir, prefix = '') =>
	readdirSync(dir).flatMap((f) => {
		const p = join(dir, f);
		return statSync(p).isDirectory() ? sizes(p, `${prefix}${f}/`) : [[`${prefix}${f}`, statSync(p).size]];
	});

for (const [file, size] of sizes(dist)) {
	console.log(`${(size / 1024).toFixed(1).padStart(8)} KB  dist/${file}`);
}

if (!existsSync(join(dist, '3d', 'hero3d.js'))) {
	process.exit(1);
}
