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
		// Handy/Tablet: eigene breite Bühne - Netz mittig und formatfüllend.
		if (small && !portrait && variant !== 'page') {
			world.position.x = -4.6;
			world.scale.setScalar(0.74);
			return;
		}
		world.position.x = portrait ? 0.6 : cfg.x;
		world.scale.setScalar(portrait ? 0.4 : cfg.scale);
	};

	// Funkenflug: orange Funken steigen auf, dazwischen kühler Lichtstaub.
	const sparkCount = small ? 120 : variant === 'page' ? 160 : 320;
	const sparkPos = new Float32Array(sparkCount * 3);
	const sparkVel = new Float32Array(sparkCount);
	for (let i = 0; i < sparkCount; i++) {
		sparkPos[i * 3] = -6 + Math.random() * 22;
		sparkPos[i * 3 + 1] = -8 + Math.random() * 16;
		sparkPos[i * 3 + 2] = -8 + Math.random() * 12;
		sparkVel[i] = 0.4 + Math.random() * 1.1;
	}
	const sparkGeo = new THREE.BufferGeometry();
	sparkGeo.setAttribute('position', new THREE.BufferAttribute(sparkPos, 3));
	const dot = (() => {
		const c = document.createElement('canvas');
		c.width = c.height = 64;
		const g = c.getContext('2d');
		const grad = g.createRadialGradient(32, 32, 0, 32, 32, 32);
		grad.addColorStop(0, 'rgba(255,255,255,1)');
		grad.addColorStop(0.3, 'rgba(255,255,255,.6)');
		grad.addColorStop(1, 'rgba(255,255,255,0)');
		g.fillStyle = grad;
		g.fillRect(0, 0, 64, 64);
		return new THREE.CanvasTexture(c);
	})();
	const sparks = new THREE.Points(sparkGeo, new THREE.PointsMaterial({ color: accent.clone().lerp(new THREE.Color('#ffd9b0'), 0.3), size: 0.22, map: dot, transparent: true, opacity: 0.9, blending: THREE.AdditiveBlending, depthWrite: false }));
	world.add(sparks);

	const dustGeo = new THREE.BufferGeometry();
	const dustPos = new Float32Array((small ? 80 : 220) * 3).map((_, i) => (i % 3 === 0 ? -8 + Math.random() * 26 : i % 3 === 1 ? -8 + Math.random() * 16 : -12 + Math.random() * 14));
	dustGeo.setAttribute('position', new THREE.BufferAttribute(dustPos, 3));
	const dust = new THREE.Points(dustGeo, new THREE.PointsMaterial({ color: 0x9cc4ff, size: 0.12, map: dot, transparent: true, opacity: 0.55, blending: THREE.AdditiveBlending, depthWrite: false }));
	world.add(dust);

	return (dt, t) => {
		// Funken steigen auf, flackern seitlich und starten unten neu.
		const p = sparkGeo.attributes.position.array;
		for (let i = 0; i < sparkCount; i++) {
			p[i * 3 + 1] += sparkVel[i] * dt;
			p[i * 3] += Math.sin(t * 2 + i) * dt * 0.25;
			if (p[i * 3 + 1] > 8) {
				p[i * 3 + 1] = -8;
			}
		}
		sparkGeo.attributes.position.needsUpdate = true;
		sparks.material.opacity = 0.9 * easeOut((t - 1.2) / 1.5);
		dust.rotation.y = t * 0.02;
		dust.position.y = Math.sin(t * 0.4) * 0.3;

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
	const red = new THREE.PointLight(0xff7a1a, 0, 14, 1.6); // Warnlicht in Orange statt Rot
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

/** Canvas-Textur aus einer Zeichenfunktion. */
function canvasTexture(w, h, draw) {
	const c = document.createElement('canvas');
	c.width = w;
	c.height = h;
	draw(c.getContext('2d'), w, h);
	const tex = new THREE.CanvasTexture(c);
	tex.colorSpace = THREE.SRGBColorSpace;
	tex.anisotropy = 4;
	return tex;
}

/** Abgerundeter Quader als Extrusion (Kanten in der Seitenansicht gerundet). */
function roundedBox(w, h, d, r) {
	const s = new THREE.Shape();
	s.moveTo(-w / 2 + r, -h / 2);
	s.lineTo(w / 2 - r, -h / 2);
	s.quadraticCurveTo(w / 2, -h / 2, w / 2, -h / 2 + r);
	s.lineTo(w / 2, h / 2 - r);
	s.quadraticCurveTo(w / 2, h / 2, w / 2 - r, h / 2);
	s.lineTo(-w / 2 + r, h / 2);
	s.quadraticCurveTo(-w / 2, h / 2, -w / 2, h / 2 - r);
	s.lineTo(-w / 2, -h / 2 + r);
	s.quadraticCurveTo(-w / 2, -h / 2, -w / 2 + r, -h / 2);
	const geo = new THREE.ExtrudeGeometry(s, { depth: d, bevelEnabled: true, bevelThickness: 0.05, bevelSize: 0.05, bevelSegments: 3, curveSegments: 8 });
	geo.translate(0, 0, -d / 2);
	return geo;
}

/**
 * Umzugs-Transporter mit Markenaufdruck, der durch eine nächtliche Stadt
 * fährt. Räder drehen sich, Straße und Häuser ziehen vorbei, hinter dem
 * Wagen schweben Umzugskartons mit Funkenspur. Scrollen gibt Gas.
 */
function buildTruck(ctx) {
	const { world, scene, still, small, accent } = ctx;
	const css = getComputedStyle(document.documentElement);
	const navyHex = (css.getPropertyValue('--navy') || '#0A2342').trim();
	const accentHex = '#' + accent.getHexString();

	const paint = new THREE.MeshPhysicalMaterial({ color: '#f3f5f8', metalness: 0.15, roughness: 0.32, clearcoat: 1, clearcoatRoughness: 0.08 });
	const cabPaint = new THREE.MeshPhysicalMaterial({ color: navyHex, metalness: 0.45, roughness: 0.3, clearcoat: 1, clearcoatRoughness: 0.06 });
	const glass = new THREE.MeshPhysicalMaterial({ color: '#0d2238', metalness: 0.9, roughness: 0.05, clearcoat: 1 });
	const rubber = new THREE.MeshStandardMaterial({ color: '#16181d', roughness: 0.85 });
	const chrome = new THREE.MeshPhysicalMaterial({ color: '#d6dde6', metalness: 1, roughness: 0.15 });
	const dark = new THREE.MeshStandardMaterial({ color: '#0b1220', roughness: 0.7 });

	// Weißes Hauptlicht, damit der Lack sauber glänzt.
	const sun = new THREE.DirectionalLight(0xffffff, 2.2);
	sun.position.set(6, 9, 8);
	scene.add(sun, new THREE.HemisphereLight(0xbfd6ff, 0x0b1220, 0.7));

	const truck = new THREE.Group();
	world.add(truck);

	// Kofferaufbau mit Beschriftung auf beiden Seiten.
	const box = new THREE.Mesh(roundedBox(4.6, 2.5, 2.3, 0.18), paint);
	box.position.set(-1.05, 1.95, 0);
	truck.add(box);

	const brand = canvasTexture(1024, 360, (g, w, h) => {
		g.clearRect(0, 0, w, h);
		g.fillStyle = navyHex;
		g.font = '800 150px Archivo, Arial, sans-serif';
		g.textBaseline = 'alphabetic';
		const name = 'Ruhrwerk';
		g.fillText(name, 40, 190);
		const nw = g.measureText(name).width;
		g.fillStyle = accentHex;
		g.fillText('24', 40 + nw, 190);
		g.fillStyle = navyHex;
		g.font = '600 58px Archivo, Arial, sans-serif';
		g.fillText('Umzug & Transport · NRW', 44, 270);
		// Orangefarbener Streifen unten
		g.fillStyle = accentHex;
		g.fillRect(0, 318, w, 30);
	});

	[1, -1].forEach((side) => {
		const decal = new THREE.Mesh(new THREE.PlaneGeometry(4.3, 1.5), new THREE.MeshStandardMaterial({ map: brand, transparent: true, roughness: 0.4 }));
		decal.position.set(-1.05, 1.82, side * 1.21);
		if (side < 0) {
			decal.rotation.y = Math.PI;
		}
		truck.add(decal);
	});

	// Fahrerhaus mit schräger Frontscheibe.
	const cabShape = new THREE.Shape();
	cabShape.moveTo(0, 0);
	cabShape.lineTo(1.75, 0);
	cabShape.lineTo(1.75, 1.05);
	cabShape.lineTo(1.25, 2.05);
	cabShape.lineTo(0, 2.05);
	cabShape.closePath();
	const cabGeo = new THREE.ExtrudeGeometry(cabShape, { depth: 2.15, bevelEnabled: true, bevelThickness: 0.08, bevelSize: 0.08, bevelSegments: 3 });
	cabGeo.translate(0, 0, -1.075);
	const cab = new THREE.Mesh(cabGeo, cabPaint);
	cab.position.set(1.32, 0.7, 0);
	truck.add(cab);

	const windshield = new THREE.Mesh(new THREE.BoxGeometry(0.05, 1.0, 1.95), glass);
	windshield.position.set(2.83, 2.25, 0);
	windshield.rotation.z = Math.atan2(0.5, 1.0);
	truck.add(windshield);

	[1, -1].forEach((side) => {
		const win = new THREE.Mesh(new THREE.BoxGeometry(0.85, 0.7, 0.05), glass);
		win.position.set(2.05, 2.3, side * 1.16);
		truck.add(win);

		// Orangefarbene Zierlinie am Fahrerhaus
		const stripe = new THREE.Mesh(new THREE.BoxGeometry(1.7, 0.12, 0.04), new THREE.MeshStandardMaterial({ color: accent, roughness: 0.4 }));
		stripe.position.set(2.15, 1.45, side * 1.17);
		truck.add(stripe);
	});

	// Fahrgestell, Stoßstange, Lichter
	const chassis = new THREE.Mesh(new THREE.BoxGeometry(6.4, 0.32, 1.9), dark);
	chassis.position.set(-0.3, 0.62, 0);
	truck.add(chassis);

	const bumper = new THREE.Mesh(new THREE.BoxGeometry(0.22, 0.32, 2.2), chrome);
	bumper.position.set(3.18, 0.75, 0);
	truck.add(bumper);

	const headMat = new THREE.MeshStandardMaterial({ color: '#fff6dc', emissive: '#ffe3a0', emissiveIntensity: 2.2 });
	const tailMat = new THREE.MeshStandardMaterial({ color: '#ff3048', emissive: '#ff1530', emissiveIntensity: 1.8 });
	[0.72, -0.72].forEach((z) => {
		const head = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.22, 0.42), headMat);
		head.position.set(3.15, 1.15, z);
		truck.add(head);
		const tail = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.3, 0.22), tailMat);
		tail.position.set(-3.42, 1.1, z * 1.35);
		truck.add(tail);
	});

	// Lichtkegel der Scheinwerfer auf der Straße
	const beam = new THREE.Mesh(
		new THREE.PlaneGeometry(5, 2.4),
		new THREE.MeshBasicMaterial({
			map: canvasTexture(256, 128, (g, w, h) => {
				const grad = g.createLinearGradient(0, 0, w, 0);
				grad.addColorStop(0, 'rgba(255,230,170,.55)');
				grad.addColorStop(1, 'rgba(255,230,170,0)');
				g.fillStyle = grad;
				g.beginPath();
				g.moveTo(0, h * 0.35);
				g.lineTo(w, 0);
				g.lineTo(w, h);
				g.lineTo(0, h * 0.65);
				g.fill();
			}),
			transparent: true,
			blending: THREE.AdditiveBlending,
			depthWrite: false,
		})
	);
	beam.rotation.x = -Math.PI / 2;
	beam.position.set(5.6, 0.03, 0);
	truck.add(beam);

	// Räder: zwei Achsen, je zwei Räder
	const wheels = [];
	const tireGeo = new THREE.CylinderGeometry(0.55, 0.55, 0.42, 32);
	tireGeo.rotateX(Math.PI / 2);
	const hubGeo = new THREE.CylinderGeometry(0.3, 0.3, 0.44, 6);
	hubGeo.rotateX(Math.PI / 2);
	[2.1, -2.2].forEach((x) => {
		[1.02, -1.02].forEach((z) => {
			const wheel = new THREE.Group();
			wheel.add(new THREE.Mesh(tireGeo, rubber), new THREE.Mesh(hubGeo, chrome));
			wheel.position.set(x, 0.55, z);
			truck.add(wheel);
			wheels.push(wheel);
		});
	});

	// Straße mit wandernden Mittelstreifen
	const roadTex = canvasTexture(512, 64, (g, w, h) => {
		g.fillStyle = '#1a2230';
		g.fillRect(0, 0, w, h);
		g.fillStyle = 'rgba(255,255,255,.85)';
		for (let x = 0; x < w; x += 128) {
			g.fillRect(x, h / 2 - 3, 72, 6);
		}
		g.fillStyle = 'rgba(255,255,255,.35)';
		g.fillRect(0, 2, w, 3);
		g.fillRect(0, h - 5, w, 3);
	});
	roadTex.wrapS = THREE.RepeatWrapping;
	roadTex.repeat.set(8, 1);
	const road = new THREE.Mesh(new THREE.PlaneGeometry(64, 5.2), new THREE.MeshStandardMaterial({ map: roadTex, roughness: 0.6, metalness: 0.1 }));
	road.rotation.x = -Math.PI / 2;
	road.position.set(0, 0, 0.4);
	world.add(road);

	// Stadt im Hintergrund: Häuser mit erleuchteten Fenstern
	const windowTex = canvasTexture(128, 256, (g, w, h) => {
		g.fillStyle = '#0c1a2e';
		g.fillRect(0, 0, w, h);
		for (let y = 12; y < h - 10; y += 22) {
			for (let x = 10; x < w - 10; x += 22) {
				const lit = Math.random();
				g.fillStyle = lit > 0.62 ? 'rgba(255,196,120,.95)' : lit > 0.5 ? 'rgba(140,190,255,.6)' : 'rgba(255,255,255,.06)';
				g.fillRect(x, y, 12, 14);
			}
		}
	});
	windowTex.wrapS = windowTex.wrapT = THREE.RepeatWrapping;
	const houseMat = new THREE.MeshStandardMaterial({ map: windowTex, emissiveMap: windowTex, emissive: '#ffffff', emissiveIntensity: 0.9, roughness: 0.8 });
	const houses = [];
	const houseCount = small ? 12 : 22;
	for (let i = 0; i < houseCount; i++) {
		const hgt = 2.5 + Math.random() * 6;
		const wid = 1.6 + Math.random() * 2.2;
		const geo = new THREE.BoxGeometry(wid, hgt, 1.6);
		const mat = houseMat.clone();
		mat.map = windowTex.clone();
		mat.map.needsUpdate = true;
		mat.map.repeat.set(wid / 2, hgt / 4);
		mat.map.offset.set(Math.random(), Math.random());
		mat.emissiveMap = mat.map;
		const house = new THREE.Mesh(geo, mat);
		house.position.set(-30 + i * (60 / houseCount) + Math.random(), hgt / 2, -6 - Math.random() * 6);
		world.add(house);
		houses.push(house);
	}

	// Schwebende Umzugskartons hinter dem Wagen
	const cardboard = canvasTexture(256, 256, (g, w, h) => {
		g.fillStyle = '#c8955e';
		g.fillRect(0, 0, w, h);
		g.fillStyle = 'rgba(0,0,0,.08)';
		for (let i = 0; i < 40; i++) {
			g.fillRect(0, Math.random() * h, w, 1);
		}
		g.fillStyle = '#d9b483';
		g.fillRect(w / 2 - 22, 0, 44, h); // Klebeband
		g.strokeStyle = 'rgba(40,25,10,.55)';
		g.lineWidth = 6;
		g.beginPath(); // Pfeile "oben"
		g.moveTo(40, 90); g.lineTo(60, 60); g.lineTo(80, 90);
		g.moveTo(60, 60); g.lineTo(60, 120);
		g.stroke();
	});
	const boxMat = new THREE.MeshStandardMaterial({ map: cardboard, roughness: 0.9 });
	const boxes = [];
	const boxCount = small ? 5 : 8;
	for (let i = 0; i < boxCount; i++) {
		const s = 0.55 + Math.random() * 0.4;
		const b = new THREE.Mesh(new THREE.BoxGeometry(s, s * 0.8, s * 0.9), boxMat);
		const base = new THREE.Vector3(-4.2 - i * 1.15, 1.6 + Math.sin(i * 1.3) * 1.3 + i * 0.12, (i % 2 ? 1 : -1) * (0.4 + Math.random() * 1.2));
		b.position.copy(base);
		b.rotation.set(Math.random() * 3, Math.random() * 3, Math.random() * 3);
		world.add(b);
		boxes.push({ b, base, phase: i * 0.8, spin: 0.4 + Math.random() * 0.6 });
	}

	// Funkenspur zwischen den Kartons
	const sparkCount = small ? 90 : 200;
	const sparkPos = new Float32Array(sparkCount * 3);
	const sparkLife = new Float32Array(sparkCount);
	const spawn = (i) => {
		sparkPos[i * 3] = -3.6 - Math.random() * 1;
		sparkPos[i * 3 + 1] = 0.8 + Math.random() * 2.8;
		sparkPos[i * 3 + 2] = (Math.random() - 0.5) * 2.4;
		sparkLife[i] = Math.random();
	};
	for (let i = 0; i < sparkCount; i++) {
		spawn(i);
		sparkPos[i * 3] -= Math.random() * 9;
	}
	const sparkGeo = new THREE.BufferGeometry();
	sparkGeo.setAttribute('position', new THREE.BufferAttribute(sparkPos, 3));
	const sparks = new THREE.Points(sparkGeo, new THREE.PointsMaterial({
		color: accent.clone().lerp(new THREE.Color('#ffd9b0'), 0.35),
		size: 0.14,
		map: canvasTexture(64, 64, (g) => {
			const grad = g.createRadialGradient(32, 32, 0, 32, 32, 32);
			grad.addColorStop(0, 'rgba(255,255,255,1)');
			grad.addColorStop(1, 'rgba(255,255,255,0)');
			g.fillStyle = grad;
			g.fillRect(0, 0, 64, 64);
		}),
		transparent: true,
		blending: THREE.AdditiveBlending,
		depthWrite: false,
	}));
	world.add(sparks);

	// Fahrtwind-Linien
	const lineMat = new THREE.MeshBasicMaterial({ color: '#9cc4ff', transparent: true, opacity: 0.35, blending: THREE.AdditiveBlending, depthWrite: false });
	const lines = [];
	for (let i = 0; i < (small ? 10 : 22); i++) {
		const l = new THREE.Mesh(new THREE.PlaneGeometry(1.5 + Math.random() * 2.5, 0.03), lineMat);
		l.position.set(-12 + Math.random() * 24, 0.4 + Math.random() * 4, -2 + Math.random() * 5);
		world.add(l);
		lines.push({ l, speed: 14 + Math.random() * 10 });
	}

	// Kamera schräg von vorn, leicht erhöht
	ctx.camera.fov = 34;
	ctx.camera.position.set(5.5, 3.1, 11.5);
	ctx.camera.lookAt(-0.6, 1.5, 0);
	ctx.camera.updateProjectionMatrix();

	ctx.layout = (portrait) => {
		world.scale.setScalar(portrait ? 0.62 : 1);
		world.position.set(portrait ? 0.4 : 0.3, portrait ? 0.3 : 0, 0);
	};

	let lastScroll = 0;
	let boost = 1;

	const step = (dt, t, c) => {
		// Scrollen gibt Gas, danach rollt der Wagen auf Reisetempo zurück.
		const ds = Math.abs(c.scroll - lastScroll);
		lastScroll = c.scroll;
		boost += (Math.min(4, 1 + ds * 400) - boost) * 0.08;
		const speed = 5.5 * boost;

		roadTex.offset.x += (dt * speed) / 8;
		wheels.forEach((w) => (w.rotation.z -= (dt * speed) / 0.55));

		// Federung und leichtes Nicken
		truck.position.y = Math.sin(t * 9) * 0.025 + Math.sin(t * 3.1) * 0.02;
		truck.rotation.z = Math.sin(t * 2.3) * 0.008;

		houses.forEach((h) => {
			h.position.x -= dt * speed * 0.35;
			if (h.position.x < -32) {
				h.position.x += 64;
			}
		});

		boxes.forEach((o) => {
			o.b.position.y = o.base.y + Math.sin(t * 1.4 + o.phase) * 0.35;
			o.b.position.x = o.base.x + Math.sin(t * 0.7 + o.phase) * 0.25;
			o.b.rotation.x += dt * o.spin * 0.6;
			o.b.rotation.y += dt * o.spin;
		});

		for (let i = 0; i < sparkCount; i++) {
			sparkPos[i * 3] -= dt * (2 + speed * 0.4);
			sparkPos[i * 3 + 1] += Math.sin(t * 3 + i) * dt * 0.2;
			if (sparkPos[i * 3] < -13) {
				spawn(i);
			}
		}
		sparkGeo.attributes.position.needsUpdate = true;

		lines.forEach((o) => {
			o.l.position.x -= dt * o.speed * (boost * 0.6);
			if (o.l.position.x < -14) {
				o.l.position.x = 12 + Math.random() * 4;
			}
		});
	};

	if (still) {
		step(0.016, 1, { scroll: 0 });
	}

	return step;
}

/**
 * Leuchtende Netzkarte NRW für den Footer: jede Stadt ein Lichtpunkt an
 * ihrer echten Position, Lichtbögen vom Firmensitz zu allen Städten, auf
 * denen orangefarbene Impulse wandern, und ein Radarring um die Zentrale.
 * Die Städte kommen als JSON aus footer.php (data-points).
 */
function buildNRW(ctx, host) {
	const { world, scene, small, accent } = ctx;
	let points = [];
	try {
		points = JSON.parse(host.dataset.points || '[]');
	} catch (e) {
		points = [];
	}
	if (!points.length) {
		return () => {};
	}

	// Projektion: Grad -> Welteinheiten, Längengrade um den Breitengrad gestaucht.
	const lat0 = points.reduce((s, p) => s + p.lat, 0) / points.length;
	const lon0 = points.reduce((s, p) => s + p.lon, 0) / points.length;
	const k = 9;
	const project = (p) => new THREE.Vector3((p.lon - lon0) * Math.cos((lat0 * Math.PI) / 180) * k, 0, -(p.lat - lat0) * k);

	const hubData = points.find((p) => p.hub) || points[0];
	const hub = project(hubData);

	const glowDot = canvasTexture(64, 64, (g) => {
		const grad = g.createRadialGradient(32, 32, 0, 32, 32, 32);
		grad.addColorStop(0, 'rgba(255,255,255,1)');
		grad.addColorStop(0.35, 'rgba(255,255,255,.5)');
		grad.addColorStop(1, 'rgba(255,255,255,0)');
		g.fillStyle = grad;
		g.fillRect(0, 0, 64, 64);
	});

	// Boden: feines Raster, das zum Rand hin ausläuft
	const grid = canvasTexture(512, 512, (g, w, h) => {
		g.clearRect(0, 0, w, h);
		g.strokeStyle = 'rgba(140,180,255,.16)';
		g.lineWidth = 1;
		for (let i = 0; i <= w; i += 32) {
			g.beginPath(); g.moveTo(i, 0); g.lineTo(i, h); g.stroke();
			g.beginPath(); g.moveTo(0, i); g.lineTo(w, i); g.stroke();
		}
		const fade = g.createRadialGradient(w / 2, h / 2, w * 0.1, w / 2, h / 2, w / 2);
		fade.addColorStop(0, 'rgba(0,0,0,0)');
		fade.addColorStop(1, 'rgba(0,0,0,1)');
		g.globalCompositeOperation = 'destination-out';
		g.fillStyle = fade;
		g.fillRect(0, 0, w, h);
	});
	const ground = new THREE.Mesh(new THREE.PlaneGeometry(24, 24), new THREE.MeshBasicMaterial({ map: grid, transparent: true, depthWrite: false }));
	ground.rotation.x = -Math.PI / 2;
	ground.position.y = -0.02;
	world.add(ground);

	// Städte: Lichtpunkt plus Lichtsäule
	const nodeGeo = new THREE.SphereGeometry(0.09, 16, 12);
	const nodeMat = new THREE.MeshBasicMaterial({ color: '#bcd6ff' });
	const hubMat = new THREE.MeshBasicMaterial({ color: accent });
	const pillarGeo = new THREE.CylinderGeometry(0.02, 0.02, 1, 8, 1, true);
	pillarGeo.translate(0, 0.5, 0);
	const pillarMat = new THREE.MeshBasicMaterial({ color: '#7fb0ff', transparent: true, opacity: 0.35, blending: THREE.AdditiveBlending, depthWrite: false });
	const pillars = [];

	points.forEach((p, i) => {
		const pos = project(p);
		const isHub = p === hubData;
		const node = new THREE.Mesh(nodeGeo, isHub ? hubMat : nodeMat);
		node.position.copy(pos);
		node.scale.setScalar(isHub ? 2 : 1);
		world.add(node);

		const halo = new THREE.Sprite(new THREE.SpriteMaterial({ map: glowDot, color: isHub ? accent : new THREE.Color('#8fb8ff'), transparent: true, opacity: isHub ? 0.9 : 0.55, blending: THREE.AdditiveBlending, depthWrite: false }));
		halo.position.copy(pos);
		halo.scale.setScalar(isHub ? 1.4 : 0.6);
		world.add(halo);

		const pillar = new THREE.Mesh(pillarGeo, pillarMat);
		pillar.position.copy(pos);
		pillar.scale.y = isHub ? 1.6 : 0.4 + (i % 5) * 0.12;
		world.add(pillar);
		pillars.push({ pillar, base: pillar.scale.y, phase: i * 0.7 });
	});

	// Namen von Zentrale und den meistgefragten Städten
	const label = (text, pos, strong) => {
		const tex = canvasTexture(512, 96, (g, w, h) => {
			g.clearRect(0, 0, w, h);
			g.font = `${strong ? 800 : 600} ${strong ? 58 : 46}px Archivo, Arial, sans-serif`;
			g.fillStyle = strong ? '#ffffff' : 'rgba(220,232,255,.85)';
			g.textAlign = 'center';
			g.textBaseline = 'middle';
			g.fillText(text, w / 2, h / 2);
		});
		const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, transparent: true, depthWrite: false }));
		sprite.position.copy(pos).add(new THREE.Vector3(0, strong ? 1.25 : 0.55, 0));
		sprite.scale.set(strong ? 2.6 : 1.9, strong ? 0.49 : 0.36, 1);
		world.add(sprite);
	};
	// Nur Städte beschriften, die weit genug auseinanderliegen - sonst
	// überlagern sich die Namen rund um die Zentrale.
	label(hubData.n, hub, true);
	const labelled = [hub];
	const minGap = small ? 2.6 : 1.9;
	points.forEach((p) => {
		const pos = project(p);
		if (p !== hubData && labelled.length < (small ? 5 : 9) && labelled.every((q) => q.distanceTo(pos) > minGap)) {
			label(p.n, pos, false);
			labelled.push(pos);
		}
	});

	// Lichtbögen von der Zentrale zu allen Städten
	const arcMat = new THREE.MeshBasicMaterial({ color: accent.clone().lerp(new THREE.Color('#ffd2a8'), 0.3), transparent: true, opacity: 0.35, blending: THREE.AdditiveBlending, depthWrite: false });
	const pulseMat = new THREE.SpriteMaterial({ map: glowDot, color: accent.clone().lerp(new THREE.Color('#fff1e0'), 0.4), transparent: true, blending: THREE.AdditiveBlending, depthWrite: false });
	const arcs = [];

	points.forEach((p, i) => {
		if (p === hubData) {
			return;
		}
		const end = project(p);
		const dist = hub.distanceTo(end);
		const mid = hub.clone().lerp(end, 0.5);
		mid.y = 0.5 + dist * 0.28;
		const curve = new THREE.QuadraticBezierCurve3(hub.clone(), mid, end);
		world.add(new THREE.Mesh(new THREE.TubeGeometry(curve, 40, 0.014, 5, false), arcMat));

		const pulse = new THREE.Sprite(pulseMat);
		pulse.scale.setScalar(0.32);
		world.add(pulse);
		arcs.push({ curve, pulse, phase: (i * 0.137) % 1, speed: 0.22 + (i % 4) * 0.04 });
	});

	// Radarringe um die Zentrale
	const rings = [0, 1, 2].map((i) => {
		const ring = new THREE.Mesh(new THREE.RingGeometry(0.3, 0.36, 64), new THREE.MeshBasicMaterial({ color: accent, transparent: true, opacity: 0.6, blending: THREE.AdditiveBlending, depthWrite: false, side: THREE.DoubleSide }));
		ring.rotation.x = -Math.PI / 2;
		ring.position.copy(hub).setY(0.01);
		world.add(ring);
		return { ring, phase: i / 3 };
	});

	scene.fog = new THREE.Fog('#061629', 14, 30);

	ctx.camera.fov = 38;
	// Auf dem Handy näher heran, damit die Karte die Bühne füllt.
	if (small) {
		ctx.camera.position.set(0, 6.4, 7.4);
	} else {
		ctx.camera.position.set(0, 8.5, 10.5);
	}
	ctx.camera.lookAt(0, 0, 0.4);
	ctx.camera.updateProjectionMatrix();

	ctx.layout = (portrait) => {
		world.scale.setScalar(portrait ? 0.8 : 1);
	};

	return (dt, t) => {
		// Karte dreht sich langsam hin und her
		world.rotation.y = Math.sin(t * 0.12) * 0.28;

		arcs.forEach((a) => {
			const u = (t * a.speed + a.phase) % 1;
			a.pulse.position.copy(a.curve.getPoint(u));
			a.pulse.material.opacity = Math.sin(u * Math.PI);
		});

		rings.forEach((r) => {
			const u = (t * 0.45 + r.phase) % 1;
			r.ring.scale.setScalar(1 + u * 9);
			r.ring.material.opacity = 0.7 * (1 - u);
		});

		pillars.forEach((p) => {
			p.pillar.scale.y = p.base * (0.85 + 0.15 * Math.sin(t * 2 + p.phase));
		});
	};
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
	const still = !!opts.still;
	const small = window.matchMedia('(max-width: 960px)').matches;
	// Eigene Variante für Handy/Tablet, z. B. Transporter statt Rohrnetz.
	const variant = (small && host.dataset.scene3dMobile) || host.dataset.scene3d || 'hero';

	const css = getComputedStyle(document.documentElement);
	const color = (name, fallback) => new THREE.Color((css.getPropertyValue(name) || '').trim() || fallback);
	const navy = color('--navy', '#0A2342');
	const accent = color('--accent', '#F4651A');
	const emergency = color('--emergency', '#C2410C');

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
	} else if (variant === 'truck') {
		update = buildTruck(ctx);
	} else if (variant === 'nrw') {
		update = buildNRW(ctx, host);
	} else {
		update = buildNetwork(ctx, variant);
	}

	const baseZ = camera.position.z;
	const baseY = camera.position.y;
	const isNetwork = !['valve', 'drops', 'truck', 'nrw'].includes(variant);

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
		if (variant !== 'nrw') {
			world.rotation.y = pointer.x * (isNetwork ? 0.16 : variant === 'truck' ? 0.2 : 0.35) + Math.sin(elapsed * 0.15) * 0.04;
		}
		world.rotation.x = baseRotX + pointer.y * 0.08 + (isNetwork ? 0.05 : 0);

		if (isNetwork) {
			// Hero-Scroll: Kamera fährt zurück und nach unten.
			const s = Math.max(0, ctx.scroll - 0.5) * 2;
			camera.position.z = baseZ + s * 4 + Math.sin(elapsed * 0.18) * 0.8;
			camera.position.y = baseY - s * 1.5 + Math.sin(elapsed * 0.23) * 0.35;
			camera.position.x = Math.sin(elapsed * 0.11) * 1.1;
			camera.lookAt(camera.position.x * 0.4, camera.position.y, 0);
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
