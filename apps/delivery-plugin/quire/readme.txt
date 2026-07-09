=== Quire ===
Contributors: manuelesposito
Tags: admin, admin theme, design, dashboard, ui
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A cleaner, warmer WordPress admin — one calm design language across your whole dashboard.

== Description ==

WordPress admin screens were built by many teams over many years, and it shows:
four clashing visual languages inside one install. Quire gathers them into one
calm, warm, consistent whole — like loose leaves bound into a book (that is
what a *quire* is).

* **Warm, bookish, precise** — a warm paper canvas, one accent colour used
  sparingly, a serif for titles and a quiet sans for the work.
* **One design language** — the classic screens, the block editor's accent,
  and WooCommerce (when present) all follow the same rules.
* **Accessible by measurement** — every text/background pair in the underlying
  design system is checked against WCAG AA in CI.
* **Private by default** — fonts are bundled with the plugin; your admin never
  requests anything from a third-party CDN.
* **An honest off switch** — one checkbox under Settings → General returns
  the default admin instantly.

Quire restyles the admin; it never restructures or forks WordPress screens.
The block editor and other React surfaces are re-accented through WordPress's
own theming hooks, never modified.

Quire is built design-system-first in the open:
https://github.com/manuelesposito/quire

== Frequently Asked Questions ==

= Does this change how anything works? =

No. Quire is colour, type, and spacing only — every screen keeps its exact
behaviour and layout. Turn it off and nothing has changed.

= Does it work with WooCommerce? =

Yes. When WooCommerce is active, Quire loads an additional stylesheet that
brings its screens into the same design language.

= Does it slow the admin down? =

Quire adds a few small stylesheets (and two bundled font files) to admin
pages only. Nothing loads on your public site.

== Changelog ==

= 0.1.0 =
* First release: design tokens, the classic wp-admin bridge, block-editor
  accent via core theming hooks, WooCommerce bridge, login screen, bundled
  fonts, off switch.
