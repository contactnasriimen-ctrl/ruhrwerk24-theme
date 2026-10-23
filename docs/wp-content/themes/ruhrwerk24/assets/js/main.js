/**
 * Ruhrwerk24 - Interaktion.
 *
 * Ohne Abhängigkeiten, ohne jQuery. Jede Funktion prüft selbst, ob ihre
 * Elemente auf der Seite vorhanden sind. Fällt das Script aus, bleibt die
 * Seite voll benutzbar: Formular, Menü-Links und Inhalte funktionieren
 * auch ohne JavaScript.
 */
(() => {
	'use strict';

	const doc = document;
	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ------------------------------------------------------------------
	 * Kopf: Schatten beim Scrollen, mobile Schnellleiste einblenden
	 * ---------------------------------------------------------------- */
	const header = doc.getElementById('header');
	const callbar = doc.getElementById('callbar');

	const onScroll = () => {
		const y = window.scrollY;
		header?.classList.toggle('is-scrolled', y > 8);
		callbar?.classList.toggle('is-visible', y > 320);
	};

	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	/* ------------------------------------------------------------------
	 * Mobiles Menü
	 * ---------------------------------------------------------------- */
	const burger = doc.getElementById('burger');
	const drawer = doc.getElementById('drawer');

	if (burger && drawer) {
		const setOpen = (open) => {
			if (open) {
				const bottom = header ? header.getBoundingClientRect().bottom : 0;
				drawer.style.setProperty('--drawer-top', `${Math.max(0, bottom)}px`);
			}

			drawer.hidden = !open;
			burger.setAttribute('aria-expanded', String(open));
			burger.setAttribute('aria-label', open ? burger.dataset.labelClose : burger.dataset.labelOpen);
			doc.body.classList.toggle('drawer-open', open);

			if (open) {
				drawer.querySelector('a')?.focus({ preventScroll: true });
			}
		};

		burger.addEventListener('click', () => setOpen(drawer.hidden));

		doc.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && !drawer.hidden) {
				setOpen(false);
				burger.focus();
			}
		});

		// Ein Klick auf einen Anker im Menü schließt es.
		drawer.addEventListener('click', (e) => {
			if (e.target.closest('a')) {
				setOpen(false);
			}
		});

		// Beim Wechsel auf Desktop-Breite nicht offen hängen bleiben.
		window.matchMedia('(min-width: 901px)').addEventListener('change', (e) => {
			if (e.matches) {
				setOpen(false);
			}
		});
	}

	/* ------------------------------------------------------------------
	 * Einblenden beim Scrollen
	 *
	 * Inhalte sind von Haus aus sichtbar. Erst wenn dieses Script läuft,
	 * setzt es "js-ready" - nur dann blendet main.css sie zum Einfliegen
	 * aus. Scheitert das Script (alter Browser, Blocker, Abbruch), bleibt
	 * jeder Text lesbar.
	 * ---------------------------------------------------------------- */
	const reveals = doc.querySelectorAll('.reveal:not(.is-in)');

	if (reduceMotion || !('IntersectionObserver' in window)) {
		reveals.forEach((el) => el.classList.add('is-in'));
	} else {
		const io = new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-in');
						io.unobserve(entry.target);
					}
				});
			},
			{ rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
		);

		// Was schon im Bild ist, bleibt einfach stehen - kein Aus- und Einblenden.
		// Sehr hohe Elemente (lange Texte auf dem Handy) ebenfalls: Sie würden
		// sonst erst spät erscheinen.
		reveals.forEach((el) => {
			const r = el.getBoundingClientRect();
			if (r.top < window.innerHeight || r.height > window.innerHeight * 0.9) {
				el.classList.add('is-in');
			} else {
				io.observe(el);
			}
		});

		doc.documentElement.classList.add('js-ready');

		// Sicherheitsnetz: Was nach dem Laden in 4 s noch nicht erschienen ist,
		// obwohl es im Bild liegt (z. B. weil der Browser ein Ereignis
		// verschluckt hat), wird direkt gezeigt.
		const sweep = () => {
			doc.querySelectorAll('.reveal:not(.is-in), .split-words.is-split:not(.is-in)').forEach((el) => {
				const r = el.getBoundingClientRect();
				if (r.top < window.innerHeight && r.bottom > 0) {
					el.classList.add('is-in');
				}
			});
		};
		window.addEventListener('scroll', () => {
			window.clearTimeout(sweep.t);
			sweep.t = window.setTimeout(sweep, 700);
		}, { passive: true });
		window.setTimeout(sweep, 4000);
	}

	/* ------------------------------------------------------------------
	 * Kennzahlen hochzählen
	 * Der Endwert steht bereits im HTML - wir zählen nur sichtbar hoch.
	 * ---------------------------------------------------------------- */
	const counters = doc.querySelectorAll('[data-count]');

	if (counters.length && !reduceMotion && 'IntersectionObserver' in window) {
		const count = (el) => {
			const target = parseInt(el.dataset.count, 10);
			const duration = 1400;
			const start = performance.now();

			const tick = (now) => {
				const p = Math.min(1, (now - start) / duration);
				const eased = 1 - Math.pow(1 - p, 3);
				el.textContent = String(Math.round(target * eased));
				if (el.dataset.text !== undefined) {
					el.dataset.text = el.textContent; // Relief-Ebenen der 3D-Ziffern mitziehen
				}

				if (p < 1) {
					requestAnimationFrame(tick);
				}
			};

			requestAnimationFrame(tick);
		};

		const co = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					count(entry.target);
					co.unobserve(entry.target);
				}
			});
		});

		counters.forEach((el) => {
			el.textContent = '0';
			if (el.dataset.text !== undefined) {
				el.dataset.text = '0';
			}
			co.observe(el);
		});
	}

	/* ------------------------------------------------------------------
	 * 3D-Szene im Seitenkopf
	 * Wird erst geladen, wenn der Browser Luft hat, und nur auf Geräten,
	 * die WebGL flüssig darstellen. Bei "Bewegung reduzieren" gibt es ein
	 * Standbild statt Animation.
	 * ---------------------------------------------------------------- */
	const scenes = doc.querySelectorAll('[data-scene3d]');

	const canRender3D = () => {
		const conn = navigator.connection;
		if (conn && (conn.saveData || /2g/.test(conn.effectiveType || ''))) {
			return false;
		}
		if (navigator.deviceMemory && navigator.deviceMemory < 4) {
			return false;
		}
		try {
			const c = doc.createElement('canvas');
			return !!(c.getContext('webgl2') || c.getContext('webgl'));
		} catch (e) {
			return false;
		}
	};

	// Nebenszenen (Ventil, Tropfen) starten auf Handy und Tablet erst, wenn
	// man in ihre Nähe scrollt - so lädt der Seitenaufbau nur eine Szene.
	const narrow = window.matchMedia('(max-width: 960px)').matches;
	const wanted = Array.from(scenes);

	const mountWhenNear = (m, el) => {
		if (!narrow || !el.hasAttribute('data-scene3d-desktop') || !('IntersectionObserver' in window)) {
			m.mount(el, { still: reduceMotion });
			return;
		}
		const near = new IntersectionObserver(([entry]) => {
			if (entry.isIntersecting) {
				near.disconnect();
				m.mount(el, { still: reduceMotion });
			}
		}, { rootMargin: '400px 0px' });
		near.observe(el);
	};

	// 3D-Hintergrund der ganzen Seite (bg3d.js) - feste Ebene hinter den
	// hellen Abschnitten. Läuft auch auf dem Handy, dort mit weniger Teilen.
	const bgHost = doc.querySelector('.bg3d');
	if (bgHost && window.rw24 && window.rw24.bg3d && canRender3D()) {
		const loadBg = () => import(window.rw24.bg3d)
			.then((m) => m.mountBackground(bgHost, { still: reduceMotion }))
			.catch(() => {});

		if ('requestIdleCallback' in window) {
			window.requestIdleCallback(loadBg, { timeout: 3000 });
		} else {
			window.setTimeout(loadBg, 1500);
		}
	}

	if (wanted.length && window.rw24 && window.rw24.scene3d && canRender3D()) {
		const load = () => import(window.rw24.scene3d)
			.then((m) => wanted.forEach((el) => mountWhenNear(m, el)))
			.catch(() => {}); // Ohne Szene bleibt der CSS-Hintergrund - kein Fehler für Besucher.

		if ('requestIdleCallback' in window) {
			window.requestIdleCallback(load, { timeout: 2500 });
		} else {
			window.setTimeout(load, 1200);
		}
	}

	/* ------------------------------------------------------------------
	 * Überschriften Wort für Wort in 3D aufbauen
	 * Der Text bleibt ein zusammenhängender Satz für Screenreader; die
	 * Wörter stecken nur in Spans für die Animation.
	 * ---------------------------------------------------------------- */
	// Seitenköpfe sofort, Abschnittsüberschriften sobald sie ins Bild kommen.
	const splitTargets = doc.querySelectorAll('.split-words, .head h2, .cta h2, .emergency__text h2, .about__text h2, .region h2');
	const splitIO = 'IntersectionObserver' in window
		? new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-in');
					splitIO.unobserve(entry.target);
				}
			});
		}, { rootMargin: '0px 0px -10% 0px' })
		: null;

	splitTargets.forEach((el) => {
		el.classList.add('split-words');

		if (reduceMotion || el.children.length) {
			el.classList.add('is-split', 'is-in');
			return;
		}

		const words = el.textContent.trim().split(/\s+/);
		el.setAttribute('aria-label', el.textContent.trim());
		el.textContent = '';

		words.forEach((word, i) => {
			const outer = doc.createElement('span');
			outer.className = 'w';
			outer.setAttribute('aria-hidden', 'true');
			const inner = doc.createElement('span');
			inner.textContent = word;
			inner.style.setProperty('--i', String(i));
			outer.appendChild(inner);
			el.appendChild(outer);
			if (i < words.length - 1) {
				el.appendChild(doc.createTextNode(' '));
			}
		});

		el.classList.add('is-split');

		if (el.closest('.hero, .service-hero, .pagehead') || !splitIO) {
			requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('is-in')));
		} else {
			splitIO.observe(el);
		}
	});

	/* ------------------------------------------------------------------
	 * Lesefortschritt oben am Rand
	 * ---------------------------------------------------------------- */
	const progressBar = doc.querySelector('.scrollbar span');

	if (progressBar) {
		let ticking = false;
		const updateProgress = () => {
			const max = doc.documentElement.scrollHeight - window.innerHeight;
			progressBar.style.transform = `scaleX(${max > 0 ? window.scrollY / max : 0})`;
			ticking = false;
		};

		window.addEventListener('scroll', () => {
			if (!ticking) {
				ticking = true;
				requestAnimationFrame(updateProgress);
			}
		}, { passive: true });
		updateProgress();
	}

	/* ------------------------------------------------------------------
	 * Elemente, die dem Scrollen folgen (--p von 0 bis 1)
	 * Die 3D-Zahlen kippen beim Scrollen ins Bild.
	 * ---------------------------------------------------------------- */
	const scrubbed = doc.querySelectorAll('.stats__list');

	if (scrubbed.length && !reduceMotion) {
		let pending = false;
		const scrub = () => {
			const vh = window.innerHeight;
			scrubbed.forEach((el) => {
				const r = el.getBoundingClientRect();
				const p = Math.min(1, Math.max(0, (vh - r.top) / (vh * 0.8)));
				el.style.setProperty('--p', p.toFixed(3));
			});
			pending = false;
		};

		window.addEventListener('scroll', () => {
			if (!pending) {
				pending = true;
				requestAnimationFrame(scrub);
			}
		}, { passive: true });
		scrub();
	} else {
		scrubbed.forEach((el) => el.style.setProperty('--p', '1'));
	}

	/* ------------------------------------------------------------------
	 * 3D-Karussell der Leistungsbereiche ("Über uns")
	 * Pfeile, Punkte, Pfeiltasten, Wischen und Klick auf eine Seitenkarte.
	 * Dreht sich alle 5 Sekunden weiter, solange niemand eingreift.
	 * ---------------------------------------------------------------- */
	doc.querySelectorAll('[data-deck]').forEach((deck) => {
		const cards = Array.from(deck.querySelectorAll('[data-deck-card]'));
		const nav = deck.querySelector('.deck__nav');
		const dots = Array.from(deck.querySelectorAll('[data-deck-dot]'));
		const total = cards.length;

		if (total < 2 || !nav) {
			return;
		}

		let active = 0;
		let timer = 0;
		let userTookOver = false;
		let paused = false;
		let inView = false;

		const show = (index) => {
			active = (index + total) % total;

			cards.forEach((card, k) => {
				let pos = (k - active + total) % total;
				if (pos > total / 2) {
					pos -= total;
				}
				const front = pos === 0;
				card.dataset.pos = Math.abs(pos) > 1 ? 'far' : String(pos);
				card.setAttribute('aria-hidden', front ? 'false' : 'true');
				// Nur die vordere Karte ist per Tastatur erreichbar.
				card.querySelectorAll('a').forEach((a) => (front ? a.removeAttribute('tabindex') : a.setAttribute('tabindex', '-1')));
			});

			dots.forEach((dot, k) => dot.setAttribute('aria-current', k === active ? 'true' : 'false'));
		};

		const takeOver = (index) => {
			userTookOver = true;
			window.clearInterval(timer);
			show(index);
		};

		deck.classList.add('is-ready');
		nav.hidden = false;
		show(0);

		deck.querySelector('[data-deck-prev]').addEventListener('click', () => takeOver(active - 1));
		deck.querySelector('[data-deck-next]').addEventListener('click', () => takeOver(active + 1));
		dots.forEach((dot, k) => dot.addEventListener('click', () => takeOver(k)));

		// Klick auf eine Seitenkarte holt sie nach vorn, statt dem Link zu folgen.
		cards.forEach((card, k) => {
			card.addEventListener('click', (e) => {
				if (card.dataset.pos !== '0') {
					e.preventDefault();
					takeOver(k);
				}
			});
		});

		deck.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowLeft') {
				takeOver(active - 1);
			} else if (e.key === 'ArrowRight') {
				takeOver(active + 1);
			}
		});

		// Wischen auf Touch-Geräten.
		let startX = null;
		const stage = deck.querySelector('.deck__stage');
		stage.addEventListener('pointerdown', (e) => {
			startX = e.clientX;
		});
		stage.addEventListener('pointerup', (e) => {
			if (startX === null) {
				return;
			}
			const dx = e.clientX - startX;
			startX = null;
			if (Math.abs(dx) > 40) {
				takeOver(active + (dx < 0 ? 1 : -1));
			}
		});

		// Selbstständig weiterdrehen - nur sichtbar, ohne Maus darüber, ohne reduzierte Bewegung.
		if (!reduceMotion) {
			deck.addEventListener('pointerenter', () => (paused = true));
			deck.addEventListener('pointerleave', () => (paused = false));
			deck.addEventListener('focusin', () => (paused = true));
			deck.addEventListener('focusout', () => (paused = false));

			if ('IntersectionObserver' in window) {
				new IntersectionObserver(([entry]) => (inView = entry.isIntersecting), { threshold: 0.4 }).observe(deck);
			} else {
				inView = true;
			}

			timer = window.setInterval(() => {
				if (!userTookOver && !paused && inView && !doc.hidden) {
					show(active + 1);
				}
			}, 5000);
		}
	});

	/* ------------------------------------------------------------------
	 * Magnetische Knöpfe: folgen dem Mauszeiger ein Stück
	 * ---------------------------------------------------------------- */
	if (!reduceMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
		doc.querySelectorAll('.hero .btn, .cta .btn, .emergency .btn, .header__cta').forEach((btn) => {
			btn.addEventListener('pointermove', (e) => {
				const r = btn.getBoundingClientRect();
				const x = (e.clientX - r.left - r.width / 2) * 0.22;
				const y = (e.clientY - r.top - r.height / 2) * 0.3;
				btn.style.translate = `${x.toFixed(1)}px ${y.toFixed(1)}px`;
			});

			btn.addEventListener('pointerleave', () => {
				btn.style.translate = '';
			});
		});
	}

	/* ------------------------------------------------------------------
	 * Karten kippen zur Maus, mit Lichtreflex
	 * Nur mit echter Maus - auf Touch-Geräten wäre es nur störend.
	 * ---------------------------------------------------------------- */
	if (!reduceMotion && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
		doc.querySelectorAll('.card, .citycard, .process__step').forEach((card) => {
			let raf = 0;

			card.addEventListener('pointermove', (e) => {
				cancelAnimationFrame(raf);
				raf = requestAnimationFrame(() => {
					const r = card.getBoundingClientRect();
					const x = (e.clientX - r.left) / r.width;
					const y = (e.clientY - r.top) / r.height;
					card.style.setProperty('--rx', `${((0.5 - y) * 9).toFixed(2)}deg`);
					card.style.setProperty('--ry', `${((x - 0.5) * 11).toFixed(2)}deg`);
					card.style.setProperty('--gx', `${(x * 100).toFixed(1)}%`);
					card.style.setProperty('--gy', `${(y * 100).toFixed(1)}%`);
					card.classList.add('is-tilting');
				});
			});

			card.addEventListener('pointerleave', () => {
				cancelAnimationFrame(raf);
				card.classList.remove('is-tilting');
				card.style.removeProperty('--rx');
				card.style.removeProperty('--ry');
			});
		});
	}

	/* ------------------------------------------------------------------
	 * Anfrageformular: eine Frage pro Schritt
	 * ---------------------------------------------------------------- */
	// Texte kommen übersetzbar aus functions.php (rw24_assets).
	const i18n = Object.assign(
		{
			required: 'Bitte wählen Sie eine Antwort.',
			zip: 'Bitte geben Sie eine fünfstellige Postleitzahl ein.',
			name: 'Bitte geben Sie Ihren Namen ein.',
			email: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
			consent: 'Bitte bestätigen Sie die Einwilligung.',
			status: 'Frage %1$d von %2$d',
		},
		window.rw24i18n || {}
	);

	const messages = {
		...i18n,
		status: (n, total) => i18n.status.replace('%1$d', n).replace('%2$d', total),
	};

	doc.querySelectorAll('[data-qf]').forEach((form) => {
		const steps = Array.from(form.querySelectorAll('.qf__step'));
		const back = form.querySelector('.qf__back');
		const next = form.querySelector('.qf__next');
		const submit = form.querySelector('.qf__submit');
		const status = form.querySelector('.qf__status');
		const total = steps.length;
		let current = 0;

		if (!total) {
			return;
		}

		// Eigene Prüfung pro Schritt statt der Browser-Blasen.
		form.noValidate = true;

		const clearError = (step) => {
			step.querySelectorAll('.qf__error').forEach((el) => el.remove());
			step.querySelectorAll('.is-invalid').forEach((el) => {
				el.classList.remove('is-invalid');
				el.removeAttribute('aria-invalid');
			});
		};

		const showError = (step, field, text) => {
			const p = doc.createElement('p');
			p.className = 'qf__error';
			p.id = `${field.id || field.name}-error`;
			p.setAttribute('role', 'alert');
			p.textContent = text;

			field.classList.add('is-invalid');
			field.setAttribute('aria-invalid', 'true');
			field.setAttribute('aria-describedby', p.id);

			const anchor = field.closest('.qf__options, .qf__field, .qf__consent') || field;
			anchor.after(p);
			field.focus({ preventScroll: false });
		};

		// Gibt true zurück, wenn der Schritt vollständig ist.
		const validate = (step) => {
			clearError(step);

			const radios = step.querySelector('input[type="radio"]');
			if (radios && !step.querySelector('input[type="radio"]:checked')) {
				showError(step, radios, messages.required);
				return false;
			}

			for (const field of step.querySelectorAll('input:not([type="radio"]):not([type="hidden"]), textarea')) {
				const value = field.type === 'checkbox' ? field.checked : field.value.trim();

				if (field.name === 'zip' && !/^\d{5}$/.test(String(value))) {
					showError(step, field, messages.zip);
					return false;
				}

				if (field.name === 'name' && String(value).length < 2) {
					showError(step, field, messages.name);
					return false;
				}

				if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(String(value))) {
					showError(step, field, messages.email);
					return false;
				}

				if (field.type === 'checkbox' && field.required && !value) {
					showError(step, field, messages.consent);
					return false;
				}
			}

			return true;
		};

		const show = (index, direction = 1, focus = true) => {
			current = Math.max(0, Math.min(total - 1, index));

			steps.forEach((step, i) => {
				step.classList.toggle('is-active', i === current);
				step.classList.toggle('is-back', i === current && direction < 0);
				step.disabled = false;
			});

			back.hidden = current === 0;
			next.hidden = current === total - 1;
			submit.hidden = current !== total - 1;

			form.style.setProperty('--progress', `${((current + 1) / total) * 100}%`);

			if (status) {
				status.textContent = messages.status(current + 1, total);
			}

			if (focus) {
				const target = steps[current].querySelector('input:not([type="hidden"]), textarea');
				target?.focus({ preventScroll: true });

				// Auf kleinen Bildschirmen den Formularkopf wieder in Sicht holen.
				const top = form.getBoundingClientRect().top;
				if (top < 0 || top > window.innerHeight * 0.6) {
					form.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
				}
			}
		};

		next.addEventListener('click', () => {
			if (validate(steps[current])) {
				show(current + 1, 1);
			}
		});

		back.addEventListener('click', () => show(current - 1, -1));

		// Auswahl getroffen? Direkt zur nächsten Frage - spart einen Klick.
		form.addEventListener('change', (e) => {
			const input = e.target;
			if (input.type !== 'radio') {
				return;
			}

			clearError(steps[current]);

			// Bei "Notfall" kurz stehen bleiben, damit der Telefon-Hinweis gelesen wird.
			if (input.value === 'notfall') {
				return;
			}

			if (current < total - 1) {
				window.setTimeout(() => show(current + 1, 1), reduceMotion ? 0 : 220);
			}
		});

		// Enter in einem Textfeld = Weiter statt Absenden.
		form.addEventListener('keydown', (e) => {
			if (e.key === 'Enter' && e.target.tagName === 'INPUT' && current < total - 1) {
				e.preventDefault();
				next.click();
			}
		});

		form.addEventListener('submit', (e) => {
			// Alle Schritte prüfen; beim ersten Fehler dorthin springen.
			for (let i = 0; i < total; i++) {
				if (!validate(steps[i])) {
					e.preventDefault();
					show(i, -1, false);
					validate(steps[i]);
					return;
				}
			}

			submit.disabled = true;
			submit.setAttribute('aria-busy', 'true');
		});

		// Bei vorausgefüllter Leistung (Leistungs- und Stadtseiten) mit Frage 2 starten.
		const preset = steps[0].querySelector('input[type="radio"]:checked');
		show(preset ? 1 : 0, 1, false);

		// Nach Zurück-Navigation aus dem Cache (bfcache) Button wieder freigeben.
		window.addEventListener('pageshow', () => {
			submit.disabled = false;
			submit.removeAttribute('aria-busy');
		});
	});

	/* ------------------------------------------------------------------
	 * "Anfrage in 60 Sekunden" im Leistungs-Hero: zum Formular springen
	 * und die erste offene Frage fokussieren.
	 * ---------------------------------------------------------------- */
	doc.querySelectorAll('.hero__jump').forEach((link) => {
		link.addEventListener('click', (e) => {
			const target = doc.querySelector(link.getAttribute('href'));
			if (!target) {
				return;
			}

			e.preventDefault();
			target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
			target.querySelector('.qf__step.is-active input:not([type="hidden"])')?.focus({ preventScroll: true });
		});
	});
})();
