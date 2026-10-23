/**
 * Ruhrwerk24 - 3D-Szene im Seitenkopf: ein Rohrnetz aus Metall, durch das
 * orangefarbene Strömung läuft, dazu schwebende Muttern und Scheiben.
 *
 * Wird von main.js erst geladen, wenn die Seite steht (kein Einfluss auf
 * die Ladezeit), und nur auf Geräten, die WebGL flüssig schaffen. Ohne
 * diese Datei bleibt der CSS-Hintergrund - die Seite sieht dann aus wie
 * vorher.
 *
 * Three.js r170 (MIT) liegt lokal im Theme, kein CDN.
 */
import * as THREE from '../vendor/three/build/three.module.min.js';
import { RoomEnvironment } from '../vendor/three/jsm/environments/RoomEnvironment.js';

const V = (x, y, z) => new THREE.Vector3(x, y, z);

/**
 * Rohrverlauf mit rechtwinkligen, gerundeten Bögen - wie echte Leitungen.
 */
function pipePath(points, bend = 0.7) {
	const path = new THREE.CurvePath();

	for (let i = 0; i < points.length - 1; i++) {
		const a = points[i];
		const b = points[i + 1];
		const dir = b.clone().sub(a).normalize();
		const start = i > 0 ? a.clone().addScaledVector(dir, bend) : a.clone();
		const end = i < points.length - 2 ? b.clone().addScaledVector(dir, -bend) : b.clone();

		path.add(new THREE.LineCurve3(start, end));

		if (i < points.length - 2) {
			const next = points[i + 2].clone().sub(b).normalize();
			path.add(new THREE.QuadraticBezierCurve3(end, b.clone(), b.clone().addScaledVector(next, bend)));
		}
	}

	return path;
}

/**
 * Leuchtende Strömungs-Textur: weiche Pulse, die in U-Richtung wandern.
 */
function flowTexture() {
	const c = document.createElement('canvas');
	c.width = 256;
	c.height = 4;
	const g = c.getContext('2d');
	const grad = g.createLinearGradient(0, 0, 256, 0);
	grad.addColorStop(0.0, '#000');
	grad.addColorStop(0.55, '#000');
	grad.addColorStop(0.8, '#fff');
	grad.addColorStop(0.86, '#fff');
	grad.addColorStop(1.0, '#000');
	g.fillStyle = grad;
	g.fillRect(0, 0, 256, 4);

	const tex = new THREE.CanvasTexture(c);
	tex.wrapS = THREE.RepeatWrapping;
	tex.wrapT = THREE.RepeatWrapping;
	return tex;
}

/**
 * Sechskantmutter als extrudierte Form mit Bohrung.
 */
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

	const geo = new THREE.ExtrudeGeometry(shape, { depth: 0.36, bevelEnabled: true, bevelThickness: 0.06, bevelSize: 0.05, bevelSegments: 3, curveSegments: 24 });
	geo.center();
	return geo;
}

/**
 * Leitungsnetz je Variante. Koordinaten in Welteinheiten, Kamera auf z=18.
 */
function layout(variant) {
	const hero = [
		{ pts: [V(-14, -2.4, -2), V(1.6, -2.4, -2), V(1.6, 1.8, -2), V(14, 1.8, -2)], r: 0.34, mat: 'steel', flow: 1 },
		{ pts: [V(-14, -4.3, 0.4), V(5.4, -4.3, 0.4), V(5.4, -4.3, -3), V(5.4, 7, -3)], r: 0.26, mat: 'accent', flow: 1.4 },
		{ pts: [V(-1.5, 8, -5), V(-1.5, 3.6, -5), V(8.6, 3.6, -5), V(8.6, -8, -5)], r: 0.42, mat: 'steel', flow: 0.8 },
		{ pts: [V(3.2, -8, 2), V(3.2, -0.6, 2), V(14, -0.6, 2)], r: 0.17, mat: 'copper', flow: 1.8 },
		{ pts: [V(-14, 5.2, -9), V(14, 5.2, -9)], r: 0.62, mat: 'dark', flow: 0.5 },
		{ pts: [V(11, -8, -1), V(11, 3.2, -1), V(11, 3.2, 3)], r: 0.22, mat: 'steel', flow: 1.1 },
	];

	const service = [
		{ pts: [V(-14, -2.2, -2), V(3, -2.2, -2), V(3, 8, -2)], r: 0.34, mat: 'steel', flow: 1 },
		{ pts: [V(-14, -3.9, 0.5), V(7.2, -3.9, 0.5), V(7.2, 2.2, 0.5), V(14, 2.2, 0.5)], r: 0.26, mat: 'accent', flow: 1.4 },
		{ pts: [V(-14, 4.8, -8), V(14, 4.8, -8)], r: 0.6, mat: 'dark', flow: 0.5 },
		{ pts: [V(10, -8, -3), V(10, 0.4, -3), V(14, 0.4, -3)], r: 0.2, mat: 'copper', flow: 1.6 },
	];

	const nuts = variant === 'service'
		? [[5.2, 3.3, 1.5, 0.9], [9.2, -1.6, 2.5, 0.7], [1.2, 1.8, 3.2, 0.55]]
		: [[4.0, 4.6, 1.4, 1], [8.0, -2.8, 3.0, 0.8], [-0.4, 0.9, 3.5, 0.6], [12.2, 4.4, -2, 1.1], [6.8, 1.0, 4.6, 0.5]];

	const washers = variant === 'service'
		? [[6.2, -0.4, 3.4, 0.7]]
		: [[2.6, -1.0, 4.4, 0.75], [10.2, 0.8, 2.0, 0.9]];

	return { pipes: variant === 'service' ? service : hero, nuts, washers };
}

/**
 * Szene in ein Element einhängen.
 *
 * @param {HTMLElement} host   Container mit data-scene3d="hero|service".
 * @param {Object}      opts   { still: true } rendert nur ein Standbild.
 */
export function mount(host, opts = {}) {
	const variant = host.dataset.scene3d || 'hero';
	const still = !!opts.still;
	const small = window.matchMedia('(max-width: 960px)').matches;

	const css = getComputedStyle(document.documentElement);
	const color = (name, fallback) => new THREE.Color((css.getPropertyValue(name) || '').trim() || fallback);
	const navy = color('--navy', '#0A2342');
	const accent = color('--accent', '#F4651A');

	// Renderer
	let renderer;
	try {
		renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'high-performance' });
	} catch (e) {
		return null;
	}

	renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, small ? 1.5 : 1.75));
	renderer.outputColorSpace = THREE.SRGBColorSpace;
	renderer.toneMapping = THREE.ACESFilmicToneMapping;
	renderer.toneMappingExposure = 1.1;
	host.appendChild(renderer.domElement);

	const scene = new THREE.Scene();
	const pmrem = new THREE.PMREMGenerator(renderer);
	scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
	pmrem.dispose();

	const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);
	camera.position.set(0, 0, 18);

	// Licht: warmes Führungslicht von rechts (Markenorange), kühles Kantenlicht.
	const key = new THREE.DirectionalLight(accent, 2.2);
	key.position.set(8, 4, 10);
	scene.add(key);
	const rim = new THREE.DirectionalLight(0x6fa8ff, 1.6);
	rim.position.set(-10, 6, -6);
	scene.add(rim);
	scene.add(new THREE.AmbientLight(navy, 0.6));

	// Materialien
	const mats = {
		steel: new THREE.MeshPhysicalMaterial({ color: navy.clone().lerp(new THREE.Color('#9fb3cc'), 0.55), metalness: 0.92, roughness: 0.26, clearcoat: 0.6, clearcoatRoughness: 0.2 }),
		dark: new THREE.MeshPhysicalMaterial({ color: navy.clone().lerp(new THREE.Color('#000000'), 0.15), metalness: 0.85, roughness: 0.4 }),
		accent: new THREE.MeshPhysicalMaterial({ color: accent, metalness: 0.35, roughness: 0.32, clearcoat: 1, clearcoatRoughness: 0.12 }),
		copper: new THREE.MeshPhysicalMaterial({ color: new THREE.Color('#c8794a'), metalness: 1, roughness: 0.22 }),
		flange: new THREE.MeshPhysicalMaterial({ color: new THREE.Color('#b9c6d6'), metalness: 1, roughness: 0.18 }),
	};

	const { pipes, nuts, washers } = layout(variant);
	const world = new THREE.Group();
	scene.add(world);

	const growers = [];
	const flows = [];
	const baseFlow = flowTexture();
	const up = V(0, 1, 0);

	pipes.forEach((p, i) => {
		const curve = pipePath(p.pts, Math.max(0.6, p.r * 2.4));
		const length = curve.getLength();
		const seg = Math.max(48, Math.round(length * 10));
		const radial = small ? 14 : 20;

		const geo = new THREE.TubeGeometry(curve, seg, p.r, radial, false);
		const mesh = new THREE.Mesh(geo, mats[p.mat]);
		world.add(mesh);

		// Wachsen beim Start: Indexbereich Segment für Segment freigeben.
		const perSeg = radial * 6;
		growers.push({ geo, total: seg * perSeg, perSeg, delay: i * 0.18 });
		geo.setDrawRange(0, still ? Infinity : 0);

		// Strömung: leuchtende Hülle mit wandernder Textur.
		const tex = baseFlow.clone();
		tex.needsUpdate = true;
		tex.repeat.set(Math.max(1, Math.round(length / 4)), 1);
		const sleeve = new THREE.Mesh(
			new THREE.TubeGeometry(curve, seg, p.r * 1.06, radial, false),
			new THREE.MeshBasicMaterial({ color: accent.clone().lerp(new THREE.Color('#ffd2a8'), 0.25), alphaMap: tex, transparent: true, opacity: 0, blending: THREE.AdditiveBlending, depthWrite: false })
		);
		world.add(sleeve);
		flows.push({ tex, mat: sleeve.material, speed: p.flow * 0.22, delay: i * 0.18 + 1.2 });

		// Flansche an den Bögen und alle paar Meter.
		const flangeGeo = new THREE.CylinderGeometry(p.r * 1.42, p.r * 1.42, Math.max(0.12, p.r * 0.5), radial);
		const count = Math.max(2, Math.floor(length / 3.2));
		for (let k = 1; k < count; k++) {
			const t = k / count;
			const flange = new THREE.Mesh(flangeGeo, mats.flange);
			flange.position.copy(curve.getPointAt(t));
			flange.quaternion.setFromUnitVectors(up, curve.getTangentAt(t).normalize());
			flange.scale.setScalar(still ? 1 : 0.001);
			flange.userData.appear = i * 0.18 + t * 1.4;
			world.add(flange);
			growers.push({ flange });
		}
	});

	// Schwebende Teile
	const floaters = [];
	const nutGeo = nutGeometry();
	nuts.forEach(([x, y, z, s], i) => {
		const m = new THREE.Mesh(nutGeo, i % 3 === 1 ? mats.accent : mats.flange);
		m.position.set(x, y, z);
		m.scale.setScalar(s);
		m.rotation.set(Math.random() * 3, Math.random() * 3, 0);
		world.add(m);
		floaters.push({ m, y, speed: 0.25 + Math.random() * 0.3, phase: Math.random() * 6, spin: 0.15 + Math.random() * 0.25 });
	});

	const washerGeo = new THREE.TorusGeometry(0.55, 0.13, 18, 48);
	washers.forEach(([x, y, z, s]) => {
		const m = new THREE.Mesh(washerGeo, mats.copper);
		m.position.set(x, y, z);
		m.scale.setScalar(s);
		world.add(m);
		floaters.push({ m, y, speed: 0.3, phase: Math.random() * 6, spin: 0.3 });
	});

	// Größe und Bildaufbau je Seitenverhältnis
	const resize = () => {
		const w = host.clientWidth;
		const h = host.clientHeight;
		if (!w || !h) {
			return;
		}
		renderer.setSize(w, h, false);
		camera.aspect = w / h;
		camera.updateProjectionMatrix();

		// Hochformat: Netz in die Mitte und kleiner, Querformat: nach rechts.
		const portrait = camera.aspect < 1;
		world.position.x = portrait ? 0.6 : variant === 'service' ? 3.2 : 2.6;
		world.scale.setScalar(portrait ? 0.4 : 0.86);
	};

	resize();
	new ResizeObserver(resize).observe(host);

	// Eingaben: Maus kippt das Netz, Scrollen fährt die Kamera zurück.
	const pointer = { x: 0, y: 0, tx: 0, ty: 0 };
	if (!still) {
		window.addEventListener('pointermove', (e) => {
			pointer.tx = (e.clientX / window.innerWidth) * 2 - 1;
			pointer.ty = (e.clientY / window.innerHeight) * 2 - 1;
		}, { passive: true });
	}

	const scrollProgress = () => {
		const r = host.getBoundingClientRect();
		return Math.min(1, Math.max(0, -r.top / Math.max(1, r.height)));
	};

	const clock = new THREE.Clock();
	let running = false;
	let frame = 0;
	let elapsed = 0;

	const easeOut = (t) => 1 - Math.pow(1 - Math.min(1, Math.max(0, t)), 3);

	const render = () => {
		const dt = Math.min(clock.getDelta(), 0.05);
		elapsed += dt;

		// Wachsen
		growers.forEach((g) => {
			if (g.geo) {
				const p = easeOut((elapsed - g.delay) / 1.8);
				const segs = Math.ceil((p * g.total) / g.perSeg);
				g.geo.setDrawRange(0, segs * g.perSeg);
			} else if (g.flange) {
				const s = easeOut((elapsed - g.flange.userData.appear) / 0.5);
				g.flange.scale.setScalar(Math.max(0.001, s));
			}
		});

		// Strömung
		flows.forEach((f) => {
			f.mat.opacity = 0.85 * easeOut((elapsed - f.delay) / 1);
			f.tex.offset.x -= dt * f.speed;
		});

		// Schweben
		floaters.forEach((f) => {
			f.m.position.y = f.y + Math.sin(elapsed * f.speed + f.phase) * 0.35;
			f.m.rotation.x += dt * f.spin;
			f.m.rotation.y += dt * f.spin * 0.7;
		});

		// Maus und Scroll weich nachführen
		pointer.x += (pointer.tx - pointer.x) * 0.04;
		pointer.y += (pointer.ty - pointer.y) * 0.04;
		world.rotation.y = pointer.x * 0.16 + Math.sin(elapsed * 0.15) * 0.04;
		world.rotation.x = pointer.y * 0.08 + 0.05;

		const s = scrollProgress();
		camera.position.z = 18 + s * 4;
		camera.position.y = -s * 1.5;
		camera.lookAt(0, -s * 1.5, 0);

		renderer.render(scene, camera);

		if (!frame++) {
			host.classList.add('is-ready');
		}
	};

	const loop = () => {
		if (!running) {
			return;
		}
		render();
		requestAnimationFrame(loop);
	};

	const start = () => {
		if (!running && !still) {
			running = true;
			clock.getDelta();
			requestAnimationFrame(loop);
		}
	};

	const stop = () => {
		running = false;
	};

	if (still) {
		elapsed = 10; // alles fertig gewachsen
		render();
		return { stop };
	}

	// Nur rendern, solange der Kopf sichtbar und der Tab aktiv ist - spart Akku.
	let visible = true;
	new IntersectionObserver(([entry]) => {
		visible = entry.isIntersecting;
		visible && !document.hidden ? start() : stop();
	}).observe(host);

	document.addEventListener('visibilitychange', () => {
		visible && !document.hidden ? start() : stop();
	});

	// WebGL-Kontext verloren (z. B. Grafiktreiber-Reset): sauber zurück zum CSS-Hintergrund.
	renderer.domElement.addEventListener('webglcontextlost', (e) => {
		e.preventDefault();
		stop();
		host.classList.remove('is-ready');
	});

	start();
	return { stop };
}
