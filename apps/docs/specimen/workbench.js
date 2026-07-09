/* =========================================================================
   Quire — workbench.js
   Injects the shared top bar (one definition for every page) and handles
   theming. Theme persists across pages via localStorage, so you can set dark
   once and click through the whole workbench while you fine-tune. A ?theme=
   URL param still wins (handy for screenshots / sharing a state).
   ========================================================================= */
(function () {
  var SCREENS = [
    ['Philosophy', 'philosophy.html'],
    ['Foundation', 'index.html'],
    ['Components', 'gallery.html'],
    ['Tokens',     'tokens.html'],
    ['Navigation', 'nav.html'],
    ['Dashboard',  'dashboard.html'],
    ['Orders',     'orders.html'],
    ['Products',   'products.html'],
    ['Settings',   'settings.html'],
    ['Jetpack',    'jetpack.html']
  ];
  var here = (location.pathname.split('/').pop() || 'index.html');

  var bar = document.createElement('header');
  bar.className = 'wb';
  var links = SCREENS.map(function (s) {
    var active = s[1] === here ? ' is-active' : '';
    return '<a class="wb__link' + active + '" href="' + s[1] + '">' + s[0] + '</a>';
  }).join('');
  bar.innerHTML =
    '<a class="wb__brand" href="home.html">Quire</a>' +
    '<nav class="wb__nav">' + links + '</nav>' +
    '<button class="wb__toggle" id="wbToggle" type="button">Dark</button>';
  document.body.insertBefore(bar, document.body.firstChild);

  var root = document.documentElement;
  var KEY = 'qr-theme';
  var btn = document.getElementById('wbToggle');
  function apply(mode) {
    root.setAttribute('data-theme', mode);
    btn.textContent = mode === 'dark' ? 'Light' : 'Dark';
    try { localStorage.setItem(KEY, mode); } catch (e) {}
  }
  var param = new URLSearchParams(location.search).get('theme');
  var stored = null;
  try { stored = localStorage.getItem(KEY); } catch (e) {}
  apply(param || stored || 'light');
  btn.addEventListener('click', function () {
    apply(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
  });

  /* ---- style trials (?trial=a|b|off) — swappable previews, light-only.
     Persists like the theme so you can click through all screens. ---- */
  var TKEY = 'qr-trial';
  var tparam = new URLSearchParams(location.search).get('trial');
  var trial = null;
  try { trial = tparam !== null ? tparam : localStorage.getItem(TKEY); } catch (e) { trial = tparam; }
  if (trial === 'off' || trial === '') trial = null;
  try { trial ? localStorage.setItem(TKEY, trial) : localStorage.removeItem(TKEY); } catch (e) {}
  if (trial === 'a' || trial === 'b') {
    apply('light');
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'trial-' + trial + '.css';
    document.head.appendChild(link);
    var chip = document.createElement('span');
    chip.textContent = 'Trial ' + trial.toUpperCase();
    chip.style.cssText = 'margin-left:10px;font-size:11px;font-weight:600;letter-spacing:.02em;padding:3px 8px;border-radius:999px;background:#111;color:#fff;align-self:center';
    bar.insertBefore(chip, btn);
  }
})();
