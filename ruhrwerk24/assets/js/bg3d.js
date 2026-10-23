/**
 * Ruhrwerk24 - 3D-Hintergrund der ganzen Seite.
 *
 * Eine feste Ebene hinter allen hellen Abschnitten: schwebende Muttern,
 * Scheiben, Rohrbögen und Rohrstücke aus Chrom, Keramikweiß und
 * Markenorange, dazu lange Leitungen mit Strömung und Lichtstaub. Beim
 * Scrollen fliegt die Kamera durch den Raum; schnelles Scrollen lässt die
 * Teile schneller drehen, die Maus verschiebt die Perspektive.
 *
 * Teile sind als InstancedMesh gebaut (ein Zeichenaufruf je Form), die
 * Szene pausiert, sobald nur dunkle Abschnitte im Bild sind, und
 * main.js lädt sie gar nicht erst auf schwachen Geräten.
 *
 * Three.js r170 (MIT), lokal im Theme.
 */
import * as THREE from '../vendor/three/build/three.module.min.js';
import { RoomEnvironment } from '../vendor/three/jsm/environments/RoomEnvironment.js';

const rand = (a, b) => a + Math.random() * (b - a);

/** Sechskantmutter. */
function nutGeometry() {
	const shape = new THREE.Shape();
	for (let i = 0; i < 6; i++) {
		const a = (i / 6) * Math.PI * 2;
		const p = [Math.cos(a) * 0.62, Math.sin(a) * 0.62];
		i === 0 ? shape.moveTo(...p) : shape.lineTo(...p);
	}
	shape.closePath();
	const hole = new THREE.Path();
	hole.absarc(0, 0, 0.3, 0, Math.PI * 2, true);
	shape.holes.push(hole);
	const geo = new THREE.ExtrudeGeometry(shape, { depth: 0.36, bevelEnabled: true, bevelThickness: 0.06, bevelSize: 0.05, bevelSegments: 2, curveSegments: 20 });
	geo.center();
	return geo;
}

/** Rohrstück mit Flanschen an beiden Enden, als eine Geometrie. */
function pipeSegmentGeometry() {
	const body = new THREE.CylinderGeometry(0.3, 0.3, 2.4, 24, 1, true);
	const f1 = new THREE.CylinderGeometry(0.46, 0.46, 0.16, 24);
	const f2 = f1.clone();
	f1.translate(0, 1.2, 0);
	f2.translate(0, -1.2, 0);
	return mergeSimple([body, f1, f2]);
}

/** Minimaler Geometrie-Verbund (nur Position/Normale/Index), spart ein Zusatzmodul. */
function mergeSimple(geos) {
	const positions = [];
	const normals = [];
	const indices = [];
	let offset = 0;

	geos.forEach((g) => {
		const geo = g.index ? g : g.toNonIndexed();
		const pos = geo.attributes.position.array;
		const nor = geo.attributes.normal.array;
		positions.push(...pos);
		normals.push(...nor);
		const idx = geo.index ? geo.index.array : [...Array(pos.length / 3).keys()];
		for (let i = 0; i < idx.length; i++) {
			indices.push(idx[i] + offset);
		}
		offset += pos.length / 3;
	});

	const merged = new THREE.BufferGeometry();
	merged.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
	merged.setAttribute('normal', new THREE.Float32BufferAttribute(normals, 3));
	merged.setIndex(indices);
	return merged;
}

/** Weicher runder Punkt für den Lichtstaub. */
function dotTexture() {
	const c = document.createElement('canvas');
	c.width = c.height = 64;
	const g = c.getContext('2d');
	const grad = g.createRadialGradient(32, 32, 0, 32, 32, 32);
	grad.addColorStop(0, 'rgba(255,255,255,1)');
	grad.addColorStop(0.4, 'rgba(255,255,255,.55)');
	grad.addColorStop(1, 'rgba(255,255,255,0)');
	g.fillStyle = grad;
	g.fillRect(0, 0, 64, 64);
	return new THREE.CanvasTexture(c);
}

/** Wandernde Pulse für die Strömung in den langen Leitungen. */
function flowTexture() {
	const c = document.createElement('canvas');
	c.width = 256;
	c.height = 4;
	const g = c.getContext('2d');
	const grad = g.createLinearGradient(0, 0, 256, 0);
	grad.addColorStop(0, '#000');
	grad.addColorStop(0.6, '#000');
	grad.addColorStop(0.82, '#fff');
	grad.addColorStop(0.88, '#fff');
	grad.addColorStop(1, '#000');
	g.fillStyle = grad;
	g.fillRect(0, 0, 256, 4);
	const tex = new THREE.CanvasTexture(c);
	tex.wrapS = tex.wrapT = THREE.RepeatWrapping;
	return tex;
}

/**
 * Hintergrund einhängen.
 *
 * @param {HTMLElement} host  Feste Ebene (.bg3d).
 * @param {Object}      opts  { still: true } = Standbild ohne Bewegung.
 */
export function mountBackground(host, opts = {}) {
	const still = !!opts.still;
	const small = window.matchMedia('(max-width: 960px)').matches;
	const root = document.documentElement;

	const css = getComputedStyle(root);
	const color = (name, fallback) => new THREE.Color((css.getPropertyValue(name) || '').trim() || fallback);
	const navy = color('--navy', '#0A2342');
	const accent = color('--accent', '#F4651A');
	const bg = color('--bg3d', '#eef2f7');

	let renderer;
	try {
		renderer = new THREE.WebGLRenderer({ antialias: !small, alpha: false, powerPreference: 'high-performance' });
	} catch (e) {
		return null;
	}

	renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, small ? 1 : 1.25));
	renderer.setClearColor(bg, 1);
	renderer.outputColorSpace = THREE.SRGBColorSpace;
	renderer.toneMapping = THREE.ACESFilmicToneMapping;
	renderer.toneMappingExposure = 1.05;
	host.appendChild(renderer.domElement);

	const scene = new THREE.Scene();
	scene.fog = new THREE.Fog(bg, 12, 48);

	const pmrem = new THREE.PMREMGenerator(renderer);
	scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
	pmrem.dispose();

	const camera = new THREE.PerspectiveCamera(48, 1, 0.1, 120);
	camera.position.set(0, 0, 12);

	const sun = new THREE.DirectionalLight(0xffffff, 1.4);
	sun.position.set(6, 10, 8);
	const warm = new THREE.DirectionalLight(accent, 0.9);
	warm.position.set(-8, -4, 6);
	scene.add(sun, warm, new THREE.HemisphereLight(0xffffff, bg, 0.8));

	// Länge des Flugs: so tief reicht der Raum.
	const DEPTH = 240;

	/* ------------------------------------------------------------------
	 * Schwebende Teile (InstancedMesh je Form)
	 * ---------------------------------------------------------------- */
	const palette = [
		new THREE.Color('#dde4ee'), // Chrom hell
		new THREE.Color('#f7f8fa'), // Keramikweiß
		new THREE.Color('#b9c6d6'), // Stahl
		accent.clone(), // Markenorange
		navy.clone().lerp(new THREE.Color('#ffffff'), 0.25), // Navy aufgehellt
	];
	const weights = [0.3, 0.22, 0.18, 0.18, 0.12];
	const pickColor = () => {
		let r = Math.random();
		for (let i = 0; i < weights.length; i++) {
			if ((r -= weights[i]) <= 0) {
				return palette[i];
			}
		}
		return palette[0];
	};

	const material = new THREE.MeshPhysicalMaterial({ metalness: 0.75, roughness: 0.22, clearcoat: 0.8, clearcoatRoughness: 0.15 });

	const shapes = [
		{ geo: nutGeometry(), count: small ? 22 : 46, scale: [0.6, 1.3] },
		{ geo: new THREE.TorusGeometry(0.55, 0.14, 16, 40), count: small ? 14 : 30, scale: [0.6, 1.2] },
		{ geo: new THREE.TorusGeometry(0.9, 0.26, 18, 36, Math.PI / 2), count: small ? 12 : 26, scale: [0.7, 1.3] },
		{ geo: pipeSegmentGeometry(), count: small ? 12 : 24, scale: [0.6, 1.1] },
	];

	const items = [];
	const dummy = new THREE.Object3D();

	shapes.forEach((shape) => {
		const mesh = new THREE.InstancedMesh(shape.geo, material, shape.count);
		mesh.instanceMatrix.setUsage(THREE.DynamicDrawUsage);

		for (let i = 0; i < shape.count; i++) {
			// Nahe Teile an den Rand, damit der Text in der Mitte frei bleibt.
			const z = rand(-DEPTH, 6);
			const side = Math.random() < 0.5 ? -1 : 1;
			const spread = small ? 5 : 16;
			const x = side * rand(small ? 2.2 : 6, spread);
			const y = rand(-8, 8);

			const item = {
				mesh,
				i,
				pos: new THREE.Vector3(x, y, z),
				rot: new THREE.Euler(rand(0, 6), rand(0, 6), rand(0, 6)),
				spin: new THREE.Vector3(rand(-0.4, 0.4), rand(-0.5, 0.5), rand(-0.3, 0.3)),
				bob: rand(0, 6.28),
				scale: rand(...shape.scale),
			};
			items.push(item);
			mesh.setColorAt(i, pickColor());
		}

		mesh.instanceColor.needsUpdate = true;
		scene.add(mesh);
	});

	/* ------------------------------------------------------------------
	 * Lange Leitungen mit Strömung entlang des Flugs
	 * ---------------------------------------------------------------- */
	const flowTex = flowTexture();
	const flows = [];
	const pipeMat = new THREE.MeshPhysicalMaterial({ color: '#cfd8e3', metalness: 0.95, roughness: 0.18, clearcoat: 0.6 });

	// Weit außen, damit die Leitungen erst im Nebel zur Bildmitte laufen.
	const lanes = small
		? [[-5.5, -6.5], [5.8, 6.8]]
		: [[-17.5, -6.5], [16.5, -5], [-15.5, 7.5], [18, 7]];

	lanes.forEach(([x0, y0], k) => {
		const pts = [];
		for (let z = 8; z >= -DEPTH - 10; z -= 24) {
			pts.push(new THREE.Vector3(x0 + Math.sin(z * 0.05 + k) * 2.4, y0 + Math.cos(z * 0.04 + k * 2) * 1.6, z));
		}
		const curve = new THREE.CatmullRomCurve3(pts);
		const seg = small ? 160 : 320;
		const radius = 0.38 + (k % 2) * 0.14;
		scene.add(new THREE.Mesh(new THREE.TubeGeometry(curve, seg, radius, small ? 12 : 18, false), pipeMat));

		const tex = flowTex.clone();
		tex.needsUpdate = true;
		tex.repeat.set(26, 1);
		const sleeve = new THREE.Mesh(
			new THREE.TubeGeometry(curve, seg, radius * 1.08, small ? 12 : 18, false),
			new THREE.MeshBasicMaterial({ color: accent, alphaMap: tex, transparent: true, opacity: 0.9, depthWrite: false })
		);
		scene.add(sleeve);
		flows.push({ tex, speed: 0.12 + k * 0.03 });

		// Flansche entlang der Leitung, ebenfalls instanziert.
		const fCount = Math.floor(curve.getLength() / 9);
		const flanges = new THREE.InstancedMesh(new THREE.CylinderGeometry(radius * 1.5, radius * 1.5, 0.22, 20), pipeMat, fCount);
		const up = new THREE.Vector3(0, 1, 0);
		for (let i = 0; i < fCount; i++) {
			const t = (i + 0.5) / fCount;
			dummy.position.copy(curve.getPointAt(t));
			dummy.quaternion.setFromUnitVectors(up, curve.getTangentAt(t));
			dummy.scale.setScalar(1);
			dummy.updateMatrix();
			flanges.setMatrixAt(i, dummy.matrix);
		}
		scene.add(flanges);
	});

	/* ------------------------------------------------------------------
	 * Lichtstaub
	 * ---------------------------------------------------------------- */
	const dustCount = small ? 260 : 700;
	const dustPos = new Float32Array(dustCount * 3);
	for (let i = 0; i < dustCount; i++) {
		dustPos[i * 3] = rand(-18, 18);
		dustPos[i * 3 + 1] = rand(-10, 10);
		dustPos[i * 3 + 2] = rand(-DEPTH, 10);
	}
	const dustGeo = new THREE.BufferGeometry();
	dustGeo.setAttribute('position', new THREE.BufferAttribute(dustPos, 3));
	const dust = new THREE.Points(dustGeo, new THREE.PointsMaterial({ color: accent, size: 0.16, map: dotTexture(), transparent: true, opacity: 0.55, depthWrite: false, sizeAttenuation: true }));
	scene.add(dust);

	/* ------------------------------------------------------------------
	 * Größe, Eingaben, Schleife
	 * ---------------------------------------------------------------- */
	const resize = () => {
		const w = window.innerWidth;
		const h = window.innerHeight;
		renderer.setSize(w, h, false);
		camera.aspect = w / h;
		camera.updateProjectionMatrix();
	};
	resize();
	window.addEventListener('resize', resize, { passive: true });

	const pointer = { x: 0, y: 0, tx: 0, ty: 0 };
	window.addEventListener('pointermove', (e) => {
		pointer.tx = (e.clientX / window.innerWidth) * 2 - 1;
		pointer.ty = (e.clientY / window.innerHeight) * 2 - 1;
	}, { passive: true });

	const scrollState = { progress: 0, last: window.scrollY, velocity: 0, camZ: 12 };
	const readScroll = () => {
		const max = Math.max(1, root.scrollHeight - window.innerHeight);
		scrollState.progress = Math.min(1, window.scrollY / max);
	};
	readScroll();
	window.addEventListener('scroll', readScroll, { passive: true });

	const clock = new THREE.Clock();
	let t = 0;
	let running = false;
	let first = true;

	const render = (dt) => {
		t += dt;

		// Scrollgeschwindigkeit glätten: schnelles Scrollen dreht die Teile schneller.
		const dy = window.scrollY - scrollState.last;
		scrollState.last = window.scrollY;
		scrollState.velocity += (Math.min(60, Math.abs(dy)) / 60 - scrollState.velocity) * 0.08;
		const boost = 1 + scrollState.velocity * 6;

		// Kamera fliegt mit dem Scrollen durch den Raum, weich nachgeführt.
		const targetZ = 12 - scrollState.progress * (DEPTH - 30);
		scrollState.camZ += (targetZ - scrollState.camZ) * (still ? 1 : 0.06);
		pointer.x += (pointer.tx - pointer.x) * 0.05;
		pointer.y += (pointer.ty - pointer.y) * 0.05;

		camera.position.set(pointer.x * 1.6 + Math.sin(t * 0.12) * 0.6, -pointer.y * 1.0 + Math.cos(t * 0.1) * 0.4, scrollState.camZ);
		camera.lookAt(pointer.x * 0.6, -pointer.y * 0.3, scrollState.camZ - 20);
		camera.rotation.z = Math.sin(t * 0.08) * 0.03;

		// Teile drehen und schweben; nur sichtbare Tiefe aktualisieren.
		const near = scrollState.camZ + 8;
		const far = scrollState.camZ - 70;
		const touched = new Set();
		items.forEach((it) => {
			if (it.pos.z > near || it.pos.z < far) {
				if (!first) {
					return;
				}
			}
			it.rot.x += it.spin.x * dt * boost;
			it.rot.y += it.spin.y * dt * boost;
			it.rot.z += it.spin.z * dt * boost;
			dummy.position.set(it.pos.x, it.pos.y + Math.sin(t * 0.6 + it.bob) * 0.4, it.pos.z);
			dummy.rotation.copy(it.rot);
			dummy.scale.setScalar(it.scale);
			dummy.updateMatrix();
			it.mesh.setMatrixAt(it.i, dummy.matrix);
			touched.add(it.mesh);
		});
		touched.forEach((m) => {
			m.instanceMatrix.needsUpdate = true;
		});

		flows.forEach((f) => {
			f.tex.offset.x -= dt * f.speed * boost;
		});

		dust.rotation.z = Math.sin(t * 0.05) * 0.1;
		dust.position.y = Math.sin(t * 0.3) * 0.3;

		renderer.render(scene, camera);

		if (first) {
			first = false;
			root.classList.add('has-bg3d');
		}
	};

	const loop = () => {
		if (!running) {
			return;
		}
		render(Math.min(clock.getDelta(), 0.05));
		requestAnimationFrame(loop);
	};

	const start = () => {
		if (!running) {
			running = true;
			clock.getDelta();
			requestAnimationFrame(loop);
		}
	};
	const stop = () => {
		running = false;
	};

	if (still) {
		render(0);
		window.addEventListener('scroll', () => requestAnimationFrame(() => render(0)), { passive: true });
		return { stop };
	}

	// Pausieren, wenn kein heller Abschnitt im Bild ist (Hintergrund verdeckt).
	const light = document.querySelectorAll('main > section:not(.on-dark), main > nav, .usp, .marquee, main.section');
	const visibleLight = new Set();
	const update = () => (visibleLight.size > 0 && !document.hidden ? start() : stop());
	const io = new IntersectionObserver((entries) => {
		entries.forEach((e) => (e.isIntersecting ? visibleLight.add(e.target) : visibleLight.delete(e.target)));
		update();
	});
	light.forEach((el) => io.observe(el));

	document.addEventListener('visibilitychange', update);

	renderer.domElement.addEventListener('webglcontextlost', (e) => {
		e.preventDefault();
		stop();
		root.classList.remove('has-bg3d');
	});

	render(0.016);
	if (!light.length) {
		start();
	}
	return { stop };
}
