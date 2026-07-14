/* Quire Shell — D6 behaviors. The column is STATELESS (every row is a
   real link; the page defines the menu), so almost nothing lives here:
   collapse (persisted per user), the account popover, the save cluster's
   floating shadow, and adopting classic screens' "Add …" button into the
   band. No client-side navigation, no menu state, ever. */
(function () {
	var shell = document.getElementById('quire-shell');
	if (!shell) return;
	var body = document.body;

	// ---- collapse: the column narrows; nothing else changes (persisted) ----
	var toggle = document.getElementById('qshell-toggle');
	function setCollapsed(collapsed, persist) {
		shell.classList.toggle('is-collapsed', collapsed);
		body.classList.toggle('quire-shell-collapsed', collapsed);
		var label = collapsed ? shell.dataset.labelExpand : shell.dataset.labelCollapse;
		if (label) {
			toggle.setAttribute('aria-label', label);
			var tip = toggle.querySelector('.qshell__tip');
			if (tip) tip.textContent = label;
		}
		if (persist) {
			var fd = new FormData();
			fd.append('action', 'quire_shell_state');
			fd.append('nonce', shell.dataset.nonce);
			fd.append('collapsed', collapsed ? '1' : '');
			fetch(shell.dataset.ajax, { method: 'POST', credentials: 'same-origin', body: fd });
		}
	}
	toggle.addEventListener('click', function () {
		setCollapsed(!shell.classList.contains('is-collapsed'), true);
	});
	// small screens start collapsed (not persisted — a viewport fact, not a choice)
	if (window.matchMedia('(max-width: 960px)').matches && !shell.classList.contains('is-collapsed')) {
		setCollapsed(true, false);
	}

	// ---- tooltips: the menu clips its own overflow (it has to — it scrolls),
	// so a tip hanging outside the column gets cut and looks buried behind the
	// content. On approach each tip is lifted to window coordinates instead —
	// same spec, same spot, nothing clipped. Gap = space-200, read from the token.
	var tipGap = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--qr-space-200')) || 8;
	function liftTip(el) {
		var tip = el.querySelector('.qshell__tip');
		if (!tip) return;
		var r = el.getBoundingClientRect();
		tip.style.position = 'fixed';
		tip.style.left = (r.right + tipGap) + 'px';
		tip.style.top = (r.top + r.height / 2) + 'px';
	}
	shell.addEventListener('mouseover', function (e) {
		var el = e.target.closest('.has-tip');
		if (el) liftTip(el);
	});
	shell.addEventListener('focusin', function (e) {
		var el = e.target.closest('.has-tip');
		if (el) liftTip(el);
	});

	// ---- account popover — float behaviors: outside click and Escape close ----
	var acct = document.getElementById('qshell-acct');
	var pop = document.getElementById('qshell-pop');
	function closePop() {
		pop.hidden = true;
		acct.setAttribute('aria-expanded', 'false');
	}
	acct.addEventListener('click', function (e) {
		e.stopPropagation();
		pop.hidden = !pop.hidden;
		acct.setAttribute('aria-expanded', String(!pop.hidden));
	});
	document.addEventListener('click', function (e) {
		if (!pop.hidden && !pop.contains(e.target)) closePop();
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && !pop.hidden) closePop();
	});

	// ---- the band ----
	var band = document.getElementById('qshell-band');
	if (band) {
		// the dirty save cluster is FIXED; it only gains the overlay shadow
		// once the band it visually sat in has scrolled away
		var ticking = false;
		var onScroll = function () {
			var savebar = document.getElementById('qsavebar');
			if (savebar) savebar.classList.toggle('is-floating', window.scrollY > 40);
			ticking = false;
		};
		window.addEventListener('scroll', function () {
			if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
		}, { passive: true });
		onScroll();

		// classic screens: the "Add …" action moves from beside the retired
		// core title into the band's actions slot
		var slot = document.getElementById('qshell-band-actions');
		document.querySelectorAll('#wpbody-content .wrap > .page-title-action').forEach(function (a) {
			slot.appendChild(a);
		});
	}
})();
