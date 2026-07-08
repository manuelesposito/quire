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
    ['Settings',   'settings.html']
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
})();
