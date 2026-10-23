/**
 * Ruhrwerk24 - 3D-Szenen (Three.js r170, MIT, lokal im Theme).
 *
 * Varianten über data-scene3d:
 *   hero     Rohrnetz mit Strömung und schwebenden Muttern (Startseite)
 *   service  dasselbe, etwas lichter (Leistungs- und Stadtseiten ohne Foto)
 *   page     schmales Rohrnetz für die Köpfe der Unterseiten
 *   valve    Absperrventil mit Handrad, das sich beim Scrollen dreht (Abschluss-Band)
 *   drops    undichte Muffe, aus der Wasser tropft, mit Blaulicht (Notdienst-Band)
 *
 * main.js lädt diese Datei erst, wenn die Seite steht, und nur auf
 * Geräten, die WebGL flüssig schaffen. Ohne sie bleibt der CSS-Hintergrund.
 */
import * as THREE from '../vendor/three/build/three.module.min.js';
import { RoomEnvironment } from '../vendor/three/jsm/environments/RoomEnvironment.js';

const V = (x, y, z) => new THREE.Vector3(x, y, z);
const UP = V(0, 1, 0);
const easeOut = (t) => 1 - Math.pow(1 - Math.min(1, Math.max(0, t)), 3);

/* ----------------------------------------------------------------------
 * Bausteine
 * -------------------------------------------------------------------- */

/** Rohrverlauf mit rechtwinkligen, gerundeten Bögen - wie echte Leitungen. */
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

/** Leuchtende Strömungs-Textur: weiche Pulse, die in U-Richtung wandern. */
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

/** Sechskantmutter als extrudierte Form mit Bohrung. */
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

/** Zylinder entlang einer Richtung ausrichten. */
function alignTo(mesh, dir) {
	mesh.quaternion.setFromUnitVectors(UP, dir.clone().normalize());
	return mesh;
}

/* ----------------------------------------------------------------------
 * Szenen-Inhalte
 * Jede Funktion baut in "ctx.world" und gibt eine update(dt, t, ctx)-
 * Funktion zurück. ctx enthält Materialien, Optionen und Hilfsdaten.
 * -------------------------------------------------------------------- */

const NETWORKS = {
	hero: {
		pipes: [
			{ pts: [V(-14, -2.4, -2), V(1.6, -2.4, -2), V(1.6, 1.8, -2), V(14, 1.8, -2)], r: 0.34, mat: 'steel', flow: 1 },
			{ pts: [V(-14, -4.3, 0.4), V(5.4, -4.3, 0.4), V(5.4, -4.3, -3), V(5.4, 7, -3)], r: 0.26, mat: 'accent', flow: 1.4 },
			{ pts: [V(-1.5, 8, -5), V(-1.5, 3.6, -5), V(8.6, 3.6, -5), V(8.6, -8, -5)], r: 0.42, mat: 'steel', flow: 0.8 },
			{ pts: [V(3.2, -8, 2), V(3.2, -0.6, 2), V(14, -0.6, 2)], r: 0.17, mat: 'copper', flow: 1.8 },
			{ pts: [V(-14, 5.2, -9), V(14, 5.2, -9)], r: 0.62, mat: 'dark', flow: 0.5 },
			{ pts: [V(11, -8, -1), V(11, 3.2, -1), V(11, 3.2, 3)], r: 0.22, mat: 'steel', flow: 1.1 },
		],
		nuts: [[4.0, 4.6, 1.4, 1], [8.0, -2.8, 3.0, 0.8], [-0.4, 0.9, 3.5, 0.6], [12.2, 4.4, -2, 1.1], [6.8, 1.0, 4.6, 0.5]],
		washers: [[2.6, -1.0, 4.4, 0.75], [10.2, 0.8, 2.0, 0.9]],
		x: 2.6,
		scale: 0.86,
	},
	service: {
		pipes: [
			{ pts: [V(-14, -2.2, -2), V(3, -2.2, -2), V(3, 8, -2)], r: 0.34, mat: 'steel', flow: 1 },
			{ pts: [V(-14, -3.9, 0.5), V(7.2, -3.9, 0.5), V(7.2, 2.2, 0.5), V(14, 2.2, 0.5)], r: 0.26, mat: 'accent', flow: 1.4 },
			{ pts: [V(-14, 4.8, -8), V(14, 4.8, -8)], r: 0.6, mat: 'dark', flow: 0.5 },
			{ pts: [V(10, -8, -3), V(10, 0.4, -3), V(14, 0.4, -3)], r: 0.2, mat: 'copper', flow: 1.6 },
		],
		nuts: [[5.2, 3.3, 1.5, 0.9], [9.2, -1.6, 2.5, 0.7], [1.2, 1.8, 3.2, 0.55]],
		washers: [[6.2, -0.4, 3.4, 0.7]],
		x: 3.2,
		scale: 0.86,
	},
	page: {
		pipes: [
			{ pts: [V(-4, -1.6, -1), V(6, -1.6, -1), V(6, 9, -1)], r: 0.3, mat: 'steel', flow: 1 },
			{ pts: [V(-4, -3.2, 1), V(9.5, -3.2, 1), V(9.5, 2.4, 1), V(16, 2.4, 1)], r: 0.22, mat: 'accent', flow: 1.5 },
			{ pts: [V(-2, 3.4, -6), V(16, 3.4, -6)], r: 0.5, mat: 'dark', flow: 0.5 },
			{ pts: [V(12.5, -8, -2), V(12.5, -0.4, -2), V(16, -0.4, -2)], r: 0.18, mat: 'copper', flow: 1.7 },
		],
		nuts: [[8, 0.6, 2.4, 0.7], [11.4, -2.0, 3.4, 0.55], [4.2, 2.2, 2.8, 0.5]],
		washers: [[13.6, 1.2, 1.6, 0.6]],
		x: 0,
		scale: 0.8,
	},
};

/** Rohrnetz mit Wachsen, Strömung, Flanschen und schwebenden Teilen. */
function buildNetwork(ctx, variant) {
	const cfg = NETWORKS[variant] || NETWORKS.hero;
	const { world, mats, still, small, accent } = ctx;
	const growers = [];
	const flows = [];
	const floaters = [];
	const baseFlow = flowTexture();

	cfg.pipes.forEach((p, i) => {
		const curve = pipePath(p.pts, Math.max(0.6, p.r * 2.4));
		const length = curve.getLength();
		const seg = Math.max(48, Math.round(length * 10));
		const radial = small ? 14 : 20;

		const geo = new THREE.TubeGeometry(curve, seg, p.r, radial, false);
		world.add(new THREE.Mesh(geo, mats[p.mat]));

		// Wachsen: Indexbereich Segment für Segment freigeben.
		const perSeg = radial * 6;
		growers.push({ geo, total: seg * perSeg, perSeg, delay: i * 0.18 });
		geo.setDrawRange(0, still ? Infinity : 0);

		// Strömung: leuchtende Hülle mit wandernder Textur.
		const tex = baseFlow.clone();
		tex.needsUpdate = true;
		tex.repeat.set(Math.max(1, Math.round(length / 4)), 1);
		const sleeve = new THREE.Mesh(
			new THREE.TubeGeometry(curve, seg, p.r * 1.06, radial, false),
			new THREE.MeshBasicMaterial({ color: accent.clone().lerp(new THREE.Color('#ffd2a8'), 0.25), alphaMap: tex, transparent: true, opacity: still ? 0.85 : 0, blending: THREE.AdditiveBlending, depthWrite: false })
		);
		world.add(sleeve);
		flows.push({ tex, mat: sleeve.material, speed: p.flow * 0.22, delay: i * 0.18 + 1.2 });

		// Flansche alle paar Meter.
		const flangeGeo = new THREE.CylinderGeometry(p.r * 1.42, p.r * 1.42, Math.max(0.12, p.r * 0.5), radial);
		const count = Math.max(2, Math.floor(length / 3.2));
		for (let k = 1; k < count; k++) {
			const t = k / count;
			const flange = alignTo(new THREE.Mesh(flangeGeo, mats.flange), curve.getTangentAt(t));
			flange.position.copy(curve.getPointAt(t));
			flange.scale.setScalar(still ? 1 : 0.001);
			flange.userData.appear = i * 0.18 + t * 1.4;
			world.add(flange);
			growers.push({ flange });
		}
	});

	const nutGeo = nutGeometry();
	cfg.nuts.forEach(([x, y, z, s], i) => {
		const m = new THREE.Mesh(nutGeo, i % 3 === 1 ? mats.accent : mats.flange);
		m.position.set(x, y, z);
		m.scale.setScalar(s);
		m.rotation.set(i * 1.3, i * 0.7, 0);
		world.add(m);
		floaters.push({ m, y, speed: 0.25 + (i % 3) * 0.12, phase: i * 1.7, spin: 0.15 + (i % 4) * 0.07 });
	});

	const washerGeo = new THREE.TorusGeometry(0.55, 0.13, 18, 48);
	cfg.washers.forEach(([x, y, z, s], i) => {
		const m = new THREE.Mesh(washerGeo, mats.copper);
		m.position.set(x, y, z);
		m.scale.setScalar(s);
		world.add(m);
		floaters.push({ m, y, speed: 0.3, phase: i * 2.3, spin: 0.3 });
	});

	ctx.layout = (portrait) => {
		world.position.x = portrait ? 0.6 : cfg.x;
		world.scale.setScalar(portrait ? 0.4 : cfg.scale);
	};

	return (dt, t) => {
		growers.forEach((g) => {
			if (g.geo) {
				const p = easeOut((t - g.delay) / 1.8);
				g.geo.setDrawRange(0, Math.ceil((p * g.total) / g.perSeg) * g.perSeg);
			} else {
				g.flange.scale.setScalar(Math.max(0.001, easeOut((t - g.flange.userData.appear) / 0.5)));
			}
		});

		flows.forEach((f) => {
			f.mat.opacity = 0.85 * easeOut((t - f.delay) / 1);
			f.tex.offset.x -= dt * f.speed;
		});

		floaters.forEach((f) => {
			f.m.position.y = f.y + Math.sin(t * f.speed + f.phase) * 0.35;
			f.m.rotation.x += dt * f.spin;
			f.m.rotation.y += dt * f.spin * 0.7;
		});
	};
}

/** Absperrventil: Rohr mit Ventilkörper, Spindel und Handrad. */
function buildValve(ctx) {
	const { world, mats } = ctx;
	const radial = 28;

	// Leitung mit Flanschen links und rechts vom Ventil.
	const pipeGeo = new THREE.CylinderGeometry(0.42, 0.42, 14, radial);
	const pipe = alignTo(new THREE.Mesh(pipeGeo, mats.steel), V(1, 0, 0));
	pipe.position.y = -2.2;
	world.add(pipe);

	[-1.55, 1.55].forEach((x) => {
		const flange = alignTo(new THREE.Mesh(new THREE.CylinderGeometry(0.78, 0.78, 0.22, radial), mats.flange), V(1, 0, 0));
		flange.position.set(x, -2.2, 0);
		world.add(flange);

		// Schrauben am Flansch.
		for (let k = 0; k < 6; k++) {
			const a = (k / 6) * Math.PI * 2;
			const bolt = alignTo(new THREE.Mesh(new THREE.CylinderGeometry(0.07, 0.07, 0.42, 10), mats.flange), V(1, 0, 0));
			bolt.position.set(x, -2.2 + Math.cos(a) * 0.6, Math.sin(a) * 0.6);
			world.add(bolt);
		}
	});

	// Ventilkörper.
	const body = new THREE.Mesh(new THREE.SphereGeometry(0.95, 40, 28), mats.accent);
	body.scale.set(1.45, 1, 1);
	body.position.y = -2.2;
	world.add(body);

	const bonnet = new THREE.Mesh(new THREE.CylinderGeometry(0.42, 0.6, 1.1, radial), mats.accent);
	bonnet.position.y = -1.0;
	world.add(bonnet);

	const stem = new THREE.Mesh(new THREE.CylinderGeometry(0.11, 0.11, 1.6, 16), mats.flange);
	stem.position.y = 0.1;
	world.add(stem);

	// Handrad: Kranz, Speichen, Nabe. Liegt waagerecht und dreht um die Spindel.
	const wheel = new THREE.Group();
	wheel.position.y = 0.85;
	world.add(wheel);

	const rim = new THREE.Mesh(new THREE.TorusGeometry(1.75, 0.17, 24, 96), mats.wheel);
	rim.rotation.x = Math.PI / 2;
	wheel.add(rim);

	for (let k = 0; k < 5; k++) {
		const a = (k / 5) * Math.PI * 2;
		const spoke = alignTo(new THREE.Mesh(new THREE.CylinderGeometry(0.075, 0.09, 1.7, 12), mats.wheel), V(Math.cos(a), 0, Math.sin(a)));
		spoke.position.set(Math.cos(a) * 0.85, 0, Math.sin(a) * 0.85);
		wheel.add(spoke);

		// Griffnoppen außen am Kranz.
		const knob = new THREE.Mesh(new THREE.SphereGeometry(0.24, 18, 12), mats.wheel);
		knob.position.set(Math.cos(a + Math.PI / 5) * 1.75, 0, Math.sin(a + Math.PI / 5) * 1.75);
		wheel.add(knob);
	}

	const hub = new THREE.Mesh(new THREE.CylinderGeometry(0.34, 0.34, 0.36, 24), mats.flange);
	wheel.add(hub);

	world.rotation.x = 0.42;

	ctx.camera.position.set(0, 0.6, 10);
	ctx.layout = (portrait) => {
		world.scale.setScalar(portrait ? 0.7 : 1);
	};

	let spin = 0;
	return (dt, t, c) => {
		// Dreht sich langsam von selbst und deutlich beim Scrollen.
		const target = c.scroll * Math.PI * 3 + t * 0.25;
		spin += (target - spin) * 0.08;
		wheel.rotation.y = spin;
		world.position.y = Math.sin(t * 0.6) * 0.12;
	};
}

/** Undichte Muffe mit fallenden Tropfen, Spritzringen und Blaulicht. */
function buildDrops(ctx) {
	const { world, mats, scene, still } = ctx;
	const radial = 28;
	const pipeY = 1.7;
	const floorY = -1.9;

	const pipe = alignTo(new THREE.Mesh(new THREE.CylinderGeometry(0.42, 0.42, 14, radial), mats.steel), V(1, 0, 0));
	pipe.position.y = pipeY;
	world.add(pipe);

	const coupling = alignTo(new THREE.Mesh(new THREE.CylinderGeometry(0.58, 0.58, 1.1, radial), mats.flange), V(1, 0, 0));
	coupling.position.y = pipeY;
	world.add(coupling);

	// Wassertropfen.
	const dropMat = new THREE.MeshPhysicalMaterial({ color: 0xa8dcff, metalness: 0, roughness: 0.04, clearcoat: 1, envMapIntensity: 2.2, transparent: true, opacity: 0.88 });
	const dropGeo = new THREE.SphereGeometry(0.13, 18, 14);
	const drops = [];
	for (let i = 0; i < 14; i++) {
		const m = new THREE.Mesh(dropGeo, dropMat);
		m.visible = false;
		world.add(m);
		drops.push({ m, y: pipeY, v: 0, wait: i * 0.32, x: (Math.random() - 0.5) * 0.5 });
	}

	// Spritzringe auf dem Boden.
	const ringMat = new THREE.MeshBasicMaterial({ color: 0x8fd0ff, transparent: true, opacity: 0, blending: THREE.AdditiveBlending, depthWrite: false, side: THREE.DoubleSide });
	const rings = [];
	for (let i = 0; i < 6; i++) {
		const r = new THREE.Mesh(new THREE.RingGeometry(0.2, 0.26, 48), ringMat.clone());
		r.rotation.x = -Math.PI / 2;
		r.position.y = floorY;
		world.add(r);
		rings.push({ r, age: 9 });
	}

	// Blaulicht: zwei rotierende Lichter, rot und blau.
	const red = new THREE.PointLight(0xff2d48, 0, 14, 1.6);
	const blue = new THREE.PointLight(0x3d8bff, 0, 14, 1.6);
	scene.add(red, blue);

	world.rotation.x = 0.18;
	ctx.camera.position.set(0, 0, 9);
	ctx.layout = (portrait) => {
		world.scale.setScalar(portrait ? 0.7 : 1);
	};

	let ringIndex = 0;

	const step = (dt, t) => {
		drops.forEach((d) => {
			if (d.wait > 0) {
				d.wait -= dt;
				return;
			}

			d.m.visible = true;
			d.v += 9.8 * dt * 0.55;
			d.y -= d.v * dt;
			d.m.position.set(d.x, d.y - 0.45, 0);
			// Im Fall gestreckt - wie echte Tropfen.
			d.m.scale.set(1, 1 + Math.min(1.4, d.v * 0.18), 1);

			if (d.y - 0.45 <= floorY) {
				const ring = rings[ringIndex++ % rings.length];
				ring.r.position.x = d.x;
				ring.age = 0;
				d.y = pipeY;
				d.v = 0;
				d.wait = 0.4 + Math.random() * 1.2;
				d.m.visible = false;
			}
		});

		rings.forEach((ring) => {
			ring.age += dt;
			const p = Math.min(1, ring.age / 1.1);
			ring.r.scale.setScalar(1 + p * 6);
			ring.r.material.opacity = 0.8 * (1 - p);
		});

		const a = t * 3.2;
		red.position.set(Math.cos(a) * 4, 1.5, Math.sin(a) * 4 + 2);
		blue.position.set(Math.cos(a + Math.PI) * 4, 1.5, Math.sin(a + Math.PI) * 4 + 2);
		red.intensity = 40 * (0.5 + 0.5 * Math.sin(a));
		blue.intensity = 40 * (0.5 - 0.5 * Math.sin(a));
	};

	if (still) {
		// Standbild: ein paar Tropfen in der Luft.
		drops.slice(0, 4).forEach((d, i) => {
			d.wait = 0;
			d.y = pipeY - i * 1.1;
			d.v = 1 + i;
		});
	}

	return step;
}

/* ----------------------------------------------------------------------
 * Einhängen
 * -------------------------------------------------------------------- */

/**
 * Szene in ein Element einhängen.
 *
 * @param {HTMLElement} host Container mit data-scene3d.
 * @param {Object}      opts { still: true } rendert nur ein Standbild.
 */
export function mount(host, opts = {}) {
	const variant = host.dataset.scene3d || 'hero';
	const still = !!opts.still;
	const small = window.matchMedia('(max-width: 960px)').matches;

	const css = getComputedStyle(document.documentElement);
	const color = (name, fallback) => new THREE.Color((css.getPropertyValue(name) || '').trim() || fallback);
	const navy = color('--navy', '#0A2342');
	const accent = color('--accent', '#F4651A');
	const emergency = color('--emergency', '#D7263D');

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

	// Licht: warmes Führungslicht in Markenorange, kühles Kantenlicht.
	const key = new THREE.DirectionalLight(accent, 2.2);
	key.position.set(8, 4, 10);
	const rim = new THREE.DirectionalLight(0x6fa8ff, 1.6);
	rim.position.set(-10, 6, -6);
	scene.add(key, rim, new THREE.AmbientLight(navy, 0.6));

	const mats = {
		steel: new THREE.MeshPhysicalMaterial({ color: navy.clone().lerp(new THREE.Color('#9fb3cc'), 0.55), metalness: 0.92, roughness: 0.26, clearcoat: 0.6, clearcoatRoughness: 0.2 }),
		dark: new THREE.MeshPhysicalMaterial({ color: navy.clone().lerp(new THREE.Color('#000000'), 0.15), metalness: 0.85, roughness: 0.4 }),
		accent: new THREE.MeshPhysicalMaterial({ color: accent, metalness: 0.35, roughness: 0.32, clearcoat: 1, clearcoatRoughness: 0.12 }),
		copper: new THREE.MeshPhysicalMaterial({ color: new THREE.Color('#c8794a'), metalness: 1, roughness: 0.22 }),
		flange: new THREE.MeshPhysicalMaterial({ color: new THREE.Color('#b9c6d6'), metalness: 1, roughness: 0.18 }),
		wheel: new THREE.MeshPhysicalMaterial({ color: emergency, metalness: 0.3, roughness: 0.3, clearcoat: 1, clearcoatRoughness: 0.1 }),
	};

	const world = new THREE.Group();
	scene.add(world);

	const ctx = { scene, camera, world, mats, still, small, accent, scroll: 0, layout: () => {} };

	let update;
	if (variant === 'valve') {
		update = buildValve(ctx);
	} else if (variant === 'drops') {
		update = buildDrops(ctx);
	} else {
		update = buildNetwork(ctx, variant);
	}

	const baseZ = camera.position.z;
	const baseY = camera.position.y;
	const isNetwork = variant !== 'valve' && variant !== 'drops';

	const resize = () => {
		const w = host.clientWidth;
		const h = host.clientHeight;
		if (!w || !h) {
			return;
		}
		renderer.setSize(w, h, false);
		camera.aspect = w / h;
		camera.updateProjectionMatrix();
		ctx.layout(camera.aspect < 1);
	};

	resize();
	new ResizeObserver(resize).observe(host);

	// Maus kippt die Szene, Scrollen bewegt die Kamera.
	const pointer = { x: 0, y: 0, tx: 0, ty: 0 };
	if (!still) {
		window.addEventListener('pointermove', (e) => {
			pointer.tx = (e.clientX / window.innerWidth) * 2 - 1;
			pointer.ty = (e.clientY / window.innerHeight) * 2 - 1;
		}, { passive: true });
	}

	// 0 = Element kommt unten ins Bild, 1 = verlässt es oben.
	const scrollProgress = () => {
		const r = host.getBoundingClientRect();
		const vh = window.innerHeight;
		return Math.min(1, Math.max(0, (vh - r.top) / (vh + r.height)));
	};

	const baseRotX = world.rotation.x;
	const clock = new THREE.Clock();
	let running = false;
	let frame = 0;
	let elapsed = still ? 10 : 0;

	const render = () => {
		const dt = still ? 0.016 : Math.min(clock.getDelta(), 0.05);
		elapsed += still ? 0 : dt;
		ctx.scroll = scrollProgress();

		update(dt, elapsed, ctx);

		pointer.x += (pointer.tx - pointer.x) * 0.04;
		pointer.y += (pointer.ty - pointer.y) * 0.04;
		world.rotation.y = pointer.x * (isNetwork ? 0.16 : 0.35) + Math.sin(elapsed * 0.15) * 0.04;
		world.rotation.x = baseRotX + pointer.y * 0.08 + (isNetwork ? 0.05 : 0);

		if (isNetwork) {
			// Hero-Scroll: Kamera fährt zurück und nach unten.
			const s = Math.max(0, ctx.scroll - 0.5) * 2;
			camera.position.z = baseZ + s * 4;
			camera.position.y = baseY - s * 1.5;
			camera.lookAt(0, camera.position.y, 0);
		}

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
		render();
		// Beim Scrollen neu zeichnen, damit Standbilder nicht verrutscht wirken.
		return { stop };
	}

	// Nur rendern, solange die Szene sichtbar und der Tab aktiv ist.
	let visible = false;
	new IntersectionObserver(([entry]) => {
		visible = entry.isIntersecting;
		visible && !document.hidden ? start() : stop();
	}, { rootMargin: '100px' }).observe(host);

	document.addEventListener('visibilitychange', () => {
		visible && !document.hidden ? start() : stop();
	});

	// WebGL-Kontext verloren: sauber zurück zum CSS-Hintergrund.
	renderer.domElement.addEventListener('webglcontextlost', (e) => {
		e.preventDefault();
		stop();
		host.classList.remove('is-ready');
	});

	// Erstes Bild sofort, auch wenn die Szene noch außerhalb liegt.
	render();
	return { stop };
}
